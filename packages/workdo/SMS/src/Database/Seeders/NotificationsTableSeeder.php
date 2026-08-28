<?php

namespace Workdo\SMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;
use App\Models\NotificationTemplateLang;

class NotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $modules = [
            'general'       => ['Create User','Create Appointment','Appointment Status Change','Appointment Reminder'],
            'SupportTicket' => ['New Ticket', 'New Ticket Reply'],
            'ToDo' => ['New To Do' , 'Complete To Do'],            
        ];

        $defaultTemplate = [
            'Create User' => [
                'variables' => '{"Company Name": "company_name","User Name": "user_name" , "business_name":"business_name"}',
                  'lang' => [
                    'ar' => 'مستخدم جديد {user_name} تم تكوينه بواسطة {company_name} في {business_name} مساحة العمل',
                    'da' => 'En ny bruger {user_name} er oprettet af {company_name} kl {business_name} arbejdsområde',
                    'de' => 'Ein neuer Benutzer {user_name} wurde erstellt von {company_name} unter {business_name} Arbeitsbereich',
                    'en' => 'A New User {user_name} has been created by {company_name} at {business_name} business',
                    'es' => 'Un nuevo usuario {user_name} ha sido creado por {company_name} en el espacio de trabajo {business_name}',
                    'fr' => 'Un nouvel utilisateur { user_name } a été créé par { company_name } dans espace de travail { business_name }',
                    'it' => 'Un Nuovo utente {user_name} è stato creato da {company_name} a {business_name} spazio di lavoro',
                    'ja' => '新規ユーザー {user_name} が {business_name} ワークスペースで {company_name} によって作成されました。',
                    'nl' => 'Een nieuwe gebruiker {user_name} is gemaakt door {company_name} op {business_name} werkgebied',
                    'pl' => 'Nowy użytkownikc {user_name} został utworzony przez {company_name} o {business_name} obszar roboczy',
                    'ru' => 'Новый пользователь {user_name} создано в {company_name} в {business_name} рабочая область',
                    'pt' => 'Um Novo Usuário {user_name} foi criado por {company_name} em {business_name} espaço de trabalho',
                    'tr' => '{company_name} tarafından {business_name} işletmesinde Yeni bir {user_name} Kullanıcısı oluşturuldu',

                ],
            ],
            'Create Appointment' => [
                'variables' => '{"Business Name": "business_name" , "Appointment Name" : "appointment_name" , "Date": "date" , "Time" : "time", "Tracking URL" : "tracking_url"}',
                  'lang' => [
                    'ar' => '{appointment_name} حجز تعيين في {date} في {time} بالنسبة الى {business_name} الأعمال التجارية 
                    تتبع موعدك هنا: {tracking_url}',
                    'da' => '{appointment_name} booker en aftale om {date} kl {time} for {business_name} forretning <br><br>
                    Følg din aftale her: {tracking_url}',
                    'de' => '{Ernennungsname} hat einen Termin am {date} um {time} für das Geschäft {business_name} <br><br>
                    Verfolgen Sie hier Ihren Termin: {tracking_url}',
                    'en' => '{appointment_name} is booking an appointment on {date} at {time} for {business_name} business. 
                    Track your appointment here: {tracking_url}',
                    'es' => '{appointment_name} está reservando una cita en {date} a las {time} para la empresa {business_name} <br><br>
                    Sigue tu cita aquí: {tracking_url}',
                    'fr' => '{appointment_name} Réservation un rendez-vous le {date} à {time} pour lentreprise {business_name} <br><br>
                    Suivez votre rendez-vous ici: {tracking_url}',
                    'it' => '{appointment_name} sta prenotando un appuntamento su {date} al {time} per {business_name} Attività commerciale <br><br>
                    Tieni traccia del tuo appuntamento qui: {tracking_url}',
                    'ja' => '{appointment_name} 予約を予約している {date} で {time} 対象 {business_name} 業務 <br><br>
                    ここで予定を追跡してください: {tracking_url}',
                    'nl' => '{appointment_name} is het boeken van een afspraak op {date} op {time} voor {business_name} Zakelijk <br><br>
                    Volg hier uw afspraak: {tracking_url}',
                    'pl' => '{appointment_name} rezerwacji jest umówionym na {date} o {time} dla {business_name} biznesowe <br><br>
                    Śledź swoje spotkanie tutaj: {tracking_url}',
                    'ru' => '{appointment_name} -это отрезка заднего отверстия {date} в {time} для {business_name} бизнес-бизнес <br><br>
                    Отслеживайте свою встречу здесь: {tracking_url}',
                    'pt' => '{appointment_name} está agendando uma consulta em {date} em {time} para {business_name} negócios <br><br>
                    Acompanhe seu agendamento aqui: {tracking_url}',
                    'tr' => '{appointment_name}, {business_name} işletmesi için {date} tarihinde {time} saatinde randevu alıyor. 
                    Randevunuzu buradan takip edin: {tracking_url}',
                ],
            ],
            'Appointment Status Change' => [
                'variables' => '{"Status": "status" , "Appointment Name" : "appointment_name"}',
                  'lang' => [
                    'ar' => 'الخاص بك {appointment_name} تم طلب التعيين {status}',
                    'da' => 'Din {appointment_name} aftaleanmodning har været {status}',
                    'de' => 'Die Terminanforderung {name_der_bestellung} wurde {status}',
                    'en' => 'Your {appointment_name} appointment request has been {status}',
                    'es' => 'La solicitud de cita de {appointment_name} ha sido {status}',
                    'fr' => 'Votre demande de nomination {appointment_name} a été {status}',
                    'it' => 'La tua richiesta di nomina {appointment_name} è stata {status}',
                    'ja' => 'ユア {appointment_name} 予約要求が行われました {status}',
                    'nl' => 'Uw {appointment_name} aanstellingsopdracht is uitgevoerd {status}',
                    'pl' => 'Twój {appointment_name} żądanie wyznaczenia zostało {status}',
                    'ru' => 'Ваш {appointment_name} заявка на назначение была {status}',
                    'pt' => 'negócios {appointment_name} pedido de nomeação foi {status}',
                    'tr' => '{appointment_name} randevu talebiniz {status} olarak gerçekleşti',
                ],
            ],
            'Appointment Reminder' => [
                'variables' => '{"Status": "status" , "Appointment Name" : "appointment_name"}',
                  'lang' => [
                    'ar' => 'الخاص بك {appointment_name} تم طلب التعيين {status}',
                    'da' => 'Din {appointment_name} aftaleanmodning har været {status}',
                    'de' => 'Die Terminanforderung {name_der_bestellung} wurde {status}',
                    'en' => 'Your {appointment_name} appointment request has been {status}',
                    'es' => 'La solicitud de cita de {appointment_name} ha sido {status}',
                    'fr' => 'Votre demande de nomination {appointment_name} a été {status}',
                    'it' => 'La tua richiesta di nomina {appointment_name} è stata {status}',
                    'ja' => 'ユア {appointment_name} 予約要求が行われました {status}',
                    'nl' => 'Uw {appointment_name} aanstellingsopdracht is uitgevoerd {status}',
                    'pl' => 'Twój {appointment_name} żądanie wyznaczenia zostało {status}',
                    'ru' => 'Ваш {appointment_name} заявка на назначение была {status}',
                    'pt' => 'negócios {appointment_name} pedido de nomeação foi {status}',
                    'tr' => '{appointment_name} randevu talebiniz {status} olarak gerçekleşti',
                ],
            ],
            'New Ticket' => [
                'variables' => '{"Ticket Name" : "ticket_name" , "Date":"date"}',
                'lang' => [
                    'ar' => '{ticket_name} تم التكوين الى {ticket_name} من {date}',
                    'da' => '{ticket_name} oprettet for {ticket_name} fra {date}',
                    'de' => '{Ticketname} erstellt für {ticket_name} von {date}',
                    'en' => '{ticket_name} created for {ticket_name} from {date}',
                    'es' => '{ticket_name} creado para {ticket_name} desde {date}',
                    'fr' => '{ticket_name} Créé pour {ticket_name} à partir de {date}',
                    'it' => '{ticket_name} creato per {ticket_name} da {date}',
                    'ja' => '{ticket_name} 作成対象 {ticket_name} からの {date}',
                    'nl' => '{ticket_name} gemaakt voor {ticket_name} van {date}',
                    'pl' => '{ticket_name} utworzone dla {ticket_name} od {date}',
                    'ru' => '{ticket_name} создано для {ticket_name} от {date}',
                    'pt' => '{ticket_name} criado para {ticket_name} de {date}',
                    'tr' => '{ticket_name}, {date} tarihinden itibaren {ticket_name} için oluşturuldu',
                ],
            ],
            'New Ticket Reply' => [
                'variables' => '{"User Name" : "user_name"}',
                'lang' => [
                    'ar' => 'رد بطاقة طلب خدمة جديد بواسطة {user_name}',
                    'da' => 'Ny ticket-svar af {user_name}',
                    'de' => 'Neue Ticket-Antwort von {user_name}',
                    'en' => 'New Ticket Reply by {user_name}',
                    'es' => 'Nuevo ticket de respuesta {user_name}',
                    'fr' => 'Nouvelle réponse au ticket par {user_name}',
                    'it' => 'Nuovo Ticket Reply by {user_name}',
                    'ja' => '新規チケットの返信 {user_name}',
                    'nl' => 'Nieuw ticket antwoord door {user_name}',
                    'pl' => 'Nowa odpowiedź zgłoszenia przez {user_name}',
                    'ru' => 'Новый ответ на паспорт по {user_name}',
                    'pt' => 'Nova Resposta de Bilhete por {user_name}',
                    'tr' => '{user_name} Tarafından Yeni Bilet Yanıtı',
                ],
            ],
            'New To Do' => [
                'variables' => '{"Company Name" : "company_name" , "Name" : "name" }',
                'lang' => [
                    'ar' => 'تم إنشاء مهمة جديدة {name} لـ {company_name}',
                    'da' => 'En ny opgave {name} er oprettet for {company_name}',
                    'de' => 'Für {company_name} wurde eine neue Aufgabe „{name}“ erstellt.',
                    'en' => 'A New To Do {name} is created for {company_name}',
                    'es' => 'Se crea una nueva tarea pendiente {nombre} para {company_name}',
                    'fr' => 'Une nouvelle tâche {name} est créée pour {company_name}',
                    'it' => 'Viene creata una nuova cosa da fare {name} per {company_name}',
                    'ja' => '{company_name} の新しい To Do {name} が作成されました',
                    'nl' => 'Er is een nieuwe taak {name} gemaakt voor {company_name}',
                    'pl' => 'Utworzono nowe zadanie do wykonania {name} dla firmy {company_name}',
                    'ru' => 'Новое задание {name} создано для {company_name}.',
                    'pt' => 'Uma nova tarefa {nome} é criada para {company_name}',
                    'tr' => '{company_name} için Yeni Yapılacaklar {name} oluşturuldu',
                ],
            ],
            'Complete To Do' => [
                'variables' => '{"User Name" : "user_name"}',
                'lang' => [
                    'ar' => 'تم إكمال المهمة بنجاح بواسطة {user_name}',
                    'da' => 'A To Do er gennemført med succes af {user_name}',
                    'de' => 'Ein To Do wird erfolgreich erledigt durch {user_name}',
                    'en' => 'A To Do is successfully completed by {user_name}',
                    'es' => 'Una tarea pendiente se completa con éxito mediante {user_name}',
                    'fr' => 'Une To Do est complétée avec succès par {user_name}',
                    'it' => 'Una cosa da fare è stata completata con successo da {user_name}',
                    'ja' => 'To Doは正常に完了しました {user_name}',
                    'nl' => 'Een To Do is succesvol afgerond door {user_name}',
                    'pl' => 'Zadanie do wykonania zostało pomyślnie ukończone przez {user_name}',
                    'ru' => 'Задача успешно завершена {user_name}',
                    'pt' => 'Uma tarefa é concluída com sucesso por{user_name}',
                    'tr' => 'Bir Yapılacak İşlem {user_name} tarafından başarıyla tamamlandı',
                ],
            ],
        ];

        foreach ($modules as $module_name => $actions) {
            foreach ($actions as $action) {
                $ntfy = Notification::where('action', $action)->where('type', 'SMS')->where('module', $module_name)->count();
                if ($ntfy == 0) {
                    $new            = new Notification();
                    $new->action    = $action;
                    $new->status    = 'on';
                    $new->module    = $module_name;
                    $new->type      = 'SMS';
                    $new->save();
                    foreach ($defaultTemplate[$action]['lang'] as $lang => $content) {
                        NotificationTemplateLang::create(
                            [
                                'parent_id' => $new->id,
                                'lang' => $lang,
                                'module' => $new->module,
                                'variables' => $defaultTemplate[$action]['variables'],
                                'content' => $content,
                            ]
                        );
                    }
                }
            }
        }
    }
}
