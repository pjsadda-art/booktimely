<?php

namespace App\Services;

use App\Models\CustomStatus;

/**
 * Classifies an appointment as a no-show, a cancellation, or neither.
 *
 * Deliberately shared by the customer reliability signal and the deposit rules
 * engine. A business that renames "No Show" to "Didn't turn up" must not get
 * different answers from the badge on the profile and the engine that decides
 * whether to take money up front — so there is exactly one classifier.
 *
 * Classification prefers the joined status *title* over the status id, and
 * falls back to the id only when the title is unusable. That survives statuses
 * being renamed, re-created, or belonging to another tenant.
 */
class AppointmentStatusClassifier
{
    public const NO_SHOW = 'no_show';
    public const CANCELLED = 'cancelled';
    public const OTHER = 'other';

    /** Title fragments that mean the customer never arrived. */
    protected const NO_SHOW_FRAGMENTS = ['no show', 'no-show', 'noshow', 'did not show'];

    /** Whole-title matches for the same, kept separate so "dnsomething" cannot match. */
    protected const NO_SHOW_EXACT = ['dns'];

    /** Substring that means the booking was called off. */
    protected const CANCELLED_FRAGMENT = 'cancel';

    /**
     * Classify from a status title, with the status id as a fallback.
     *
     * @param  string|null  $title      the joined `custom_statuses.title`
     * @param  int|null     $statusId   used only when the title is unusable
     * @param  array<int,string>  $idSlugMap  status id => slug, for the fallback
     */
    public function classify(?string $title, $statusId = null, array $idSlugMap = []): string
    {
        $normalised = trim(mb_strtolower((string) $title));

        if ($normalised !== '') {
            return $this->fromText($normalised);
        }

        // Title unusable: the row may have been deleted, or belong to a tenant
        // this query could not join. Slug is the next most stable identity.
        $slug = $statusId !== null && isset($idSlugMap[$statusId]) ? $idSlugMap[$statusId] : null;

        if (!empty($slug)) {
            return $this->fromText(str_replace('-', ' ', mb_strtolower($slug)));
        }

        return self::OTHER;
    }

    /**
     * The classification rules themselves, applied to already-lowercased text.
     * No-show is tested first: "cancelled - no show" is a no-show.
     */
    protected function fromText(string $text): string
    {
        if (in_array($text, self::NO_SHOW_EXACT, true)) {
            return self::NO_SHOW;
        }

        foreach (self::NO_SHOW_FRAGMENTS as $fragment) {
            if (str_contains($text, $fragment)) {
                return self::NO_SHOW;
            }
        }

        if (str_contains($text, self::CANCELLED_FRAGMENT)) {
            return self::CANCELLED;
        }

        return self::OTHER;
    }

    /**
     * status id => slug for one tenant, used by the id fallback above.
     *
     * @return array<int,string>
     */
    public function slugMap($businessId, $createdBy): array
    {
        static $cache = [];
        $key = $businessId . ':' . $createdBy;

        if (!isset($cache[$key])) {
            $cache[$key] = CustomStatus::where('business_id', $businessId)
                ->where('created_by', $createdBy)
                ->pluck('slug', 'id')
                ->filter()
                ->all();
        }

        return $cache[$key];
    }
}
