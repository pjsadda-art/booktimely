@extends('layouts.main')

@section('page-title')
    {{ __('Blog') }}
@endsection
@section('page-breadcrumb')
    {{ Module_Alias_Name($id) }},
    {{ __('Blog') }}
@endsection
@section('page-action')
    <div>
        @permission('blog create')
            <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md"
                data-title="{{ __('Create New Blog') }}" data-url="{{ route('blog.create',[$id,$businessID]) }}" data-bs-toggle="tooltip"
                data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@section('content')
    <!-- [ Main Content ] start -->
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <div class="table-responsive booking-data-table">
                        <table class="table mb-0 pc-dt-simple" id="pc-dt-simple">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Image') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th class="text-end me-3">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($blogs as $key => $blog)
                                <tr>
                                        <td><span class="white-space">{{ ++$key }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ check_file($blog->image) ? get_file($blog->image) : get_file('uploads/default/avatar.png') }}"
                                                    class="wid-30 rounded-circle me-3"
                                                    alt="avatar image" height="30">
                                            </div>
                                        </td>
                                        <td class="booking-data-res"><span class="white-space">{{ $blog->title }}</span></td>
                                        <td><span class="white-space">{{ $blog->date }}</span></td>
                                        <td class="booking-data-res"><span class="white-space">{{ $blog->description }}</span></td>

                                        <td>
                                            <div class="d-flex justify-content-end">
                                            @permission('blog edit')
                                            <div class="action-btn me-2">
                                                <a href="#" class="btn btn-sm  bg-info d-inline align-items-center"
                                                    data-url="{{ route('blogs.edit', $blog->id) }}"
                                                    class="dropdown-item" data-ajax-popup="true"
                                                    data-title="{{ __('Edit Blog') }}" data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('Edit') }}">
                                                    <span class="text-white"> <i class="ti ti-pencil"></i></span></a>
                                            </div>
                                            @endpermission
                                            @permission('blog delete')
                                            <div class="action-btn">
                                                <form method="POST"
                                                    action="{{ route('blogs.destroy', $blog->id) }}"
                                                    id="user-form-{{ $blog->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input name="_method" type="hidden" value="DELETE">
                                                    <button type="button"
                                                        class="btn btn-sm bg-danger d-inline align-items-center show_confirm"
                                                        data-bs-toggle="tooltip" title='Delete'>
                                                        <span class="text-white"> <i class="ti ti-trash"></i></span>
                                                    </button>
                                                </form>
                                            </div>
                                            @endpermission
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->

@endsection

