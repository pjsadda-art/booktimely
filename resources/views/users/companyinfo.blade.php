<div class="modal-body">
    <div class="row">
        <div class="col-12 col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row row-gaps">
                        <div class="col-sm-4 col-12 text-center">
                            <h6 >{{ 'Total Business' }}</h6>
                            <p class=" text-sm mb-0">
                                <i
                                    class="ti ti-users text-warning card-icon-text-space fs-5 mx-1"></i><span class="total_business fs-5">
                                    {{ $business_data['total_business'] }}</span>
                            </p>
                        </div>
                        <div class="col-sm-4 col-12 text-center">
                            <h6 >{{ 'Active Business' }}</h6>
                            <p class=" text-sm mb-0">
                                <i
                                    class="ti ti-users text-primary card-icon-text-space fs-5 mx-1"></i><span class="active_business fs-5">{{ $business_data['active_business'] }}</span>
                            </p>
                        </div>
                        <div class="col-sm-4 col-12 text-center">
                            <h6 >{{ 'Disable Business' }}</h6>
                            <p class=" text-sm mb-0">
                                <i
                                    class="ti ti-users text-danger card-icon-text-space fs-5 mx-1"></i><span class="disable_business fs-5">{{ $business_data['disable_business'] }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="row row-gaps justify-content-center">
                <div class="col-sm-12 col-md-10 col-xxl-12 col-md-12">
                    <div class="p-3 card m-0">
                        <ul class="nav nav-pills nav-fill" id="pills-tab" role="tablist">
                            @foreach ($users_data as $key => $user_data)
                                @php
                                    $business = \App\Models\Business::where('id', $user_data['business_id'])->first();
                                @endphp
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link text-capitalize {{ $loop->index == 0 ? 'active' : '' }}"
                                        id="pills-{{ strtolower($business->id) }}-tab" data-bs-toggle="pill"
                                        data-bs-target="#pills-{{ strtolower($business->id) }}"
                                        type="button">{{ $business->name }}</button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="px-0 card-body">
                        <div class="tab-content" id="pills-tabContent">
                            @foreach ($users_data as $key => $user_data)
                            @php
                                $users = \App\Models\User::where('created_by', $id)
                                    ->where('business_id', $user_data['business_id'])
                                    ->get();
                                $business = \App\Models\Business::where('id', $user_data['business_id'])->first();
                            @endphp
                                <div class="tab-pane text-capitalize fade show {{ $loop->index == 0 ? 'active' : '' }}"
                                    id="pills-{{ strtolower($business->id) }}" role="tabpanel"
                                    aria-labelledby="pills-{{ strtolower($business->id) }}-tab">

                                    <div class="row">
                                        <div class="col-lg-11 col-md-10 col-sm-10 mt-3 text-end">
                                        <small class="text-danger my-3">{{__('* Please ensure that if you disable the business, all users within this business are also disabled.')}}</small>

                                        </div>
                                        <div class="col-lg-1 col-md-2 col-sm-2 text-end">
                                            <div class="text-end">
                                                <div class="form-check form-switch custom-switch-v1 mt-3">
                                                    <input type="checkbox" name="business_disable"
                                                        class="form-check-input input-primary is_disable" value="1"
                                                        data-id="{{ $user_data['business_id'] }}" data-company="{{ $id }}"
                                                        data-name="{{ __('business') }}"
                                                        {{ $business->is_disable == 1 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="business_disable"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row business row-gaps"  data-business-id ={{ $business->id }} data-url="{{ route('user.unable') }}">
                                            <div class="col-sm-4 col-12 text-center">
                                                <p class="text-sm mb-0"
                                                    data-bs-original-title="{{ __('Total Users') }}"><i
                                                        class="ti ti-users text-warning card-icon-text-space fs-5 mx-1"></i><span class="total_users fs-5">{{ $user_data['total_users'] }}</span>

                                                </p>
                                            </div>
                                            <div class="col-sm-4 col-12 text-center">
                                                <p class="text-sm mb-0"
                                                    data-bs-original-title="{{ __('Active Users') }}"><i
                                                        class="ti ti-users text-primary card-icon-text-space fs-5 mx-1"></i><span class="active_users fs-5">{{ $user_data['active_users'] }}</span>
                                                </p>
                                            </div>
                                            <div class="col-sm-4 col-12 text-center">
                                                <p class="text-sm mb-0"
                                                    data-bs-original-title="{{ __('Disable Users') }}"><i
                                                        class="ti ti-users text-danger card-icon-text-space fs-5 mx-1"></i><span class="disable_users fs-5">{{ $user_data['disable_users'] }}</span>
                                                </p>
                                            </div>
                                    </div>
                                    <div class="row my-2 " id="user_section_{{$business->id}}">
                                        @foreach ($users as $user)
                                            <div class="col-md-6 my-2 ">
                                                <div
                                                    class="d-flex align-items-center justify-content-between list_colume_notifi pb-2">
                                                    <div class="mb-3 mb-sm-0">
                                                        <h6>
                                                            <img src="{{ check_file($user->avatar) ? get_file($user->avatar) : get_file('uploads/users-avatar/avatar.png') }}"
                                                                class=" wid-30 rounded-circle mx-2" alt="image"
                                                                height="30">
                                                            <label for="user"
                                                                class="form-label">{{ $user->name }}</label>
                                                        </h6>
                                                    </div>
                                                    <div class="text-end ">
                                                        <div class="form-check form-switch custom-switch-v1 mb-2">
                                                            <input type="checkbox" name="user_disable"
                                                                class="form-check-input input-primary is_disable"
                                                                value="1" data-id='{{ $user->id }}' data-company="{{ $id }}"
                                                                data-name="{{ __('user') }}"
                                                                {{ $user->is_disable == 1 ? 'checked' : '' }} {{ $business->is_disable == 1 ? '' : 'disabled' }}>
                                                            <label class="form-check-label" for="user_disable"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

