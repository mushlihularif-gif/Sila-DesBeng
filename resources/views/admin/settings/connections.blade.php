@extends('admin.dashboard.index')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Account Settings / </span> Connections
    </h4>

    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-pills flex-column flex-md-row mb-3">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.profile') }}">
                        <i class="bx bx-user me-1"></i> Account
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.settings.notifications') }}">
                        <i class="bx bx-bell me-1"></i> Notifications
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="javascript:void(0);">
                        <i class="bx bx-link-alt me-1"></i> Connections
                    </a>
                </li>
            </ul>

            <div class="row">
                <!-- Connected Accounts -->
                <div class="col-md-6 col-12 mb-md-0 mb-4">
                    <div class="card">
                        <h5 class="card-header">Connected Accounts</h5>
                        <div class="card-body">
                            <p>Display content from your connected accounts on your site</p>
                            
                            <!-- Google -->
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('Admin/img/icons/brands/google.png') }}" alt="google" class="me-3" height="30" />
                                </div>
                                <div class="flex-grow-1 row">
                                    <div class="col-9 mb-sm-0 mb-2">
                                        <h6 class="mb-0">Google</h6>
                                        <small class="text-muted">Calendar and contacts</small>
                                    </div>
                                    <div class="col-3 text-end">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input float-end" type="checkbox" role="switch" id="googleSwitch" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Slack -->
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('Admin/img/icons/brands/slack.png') }}" alt="slack" class="me-3" height="30" />
                                </div>
                                <div class="flex-grow-1 row">
                                    <div class="col-9 mb-sm-0 mb-2">
                                        <h6 class="mb-0">Slack</h6>
                                        <small class="text-muted">Communication</small>
                                    </div>
                                    <div class="col-3 text-end">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input float-end" type="checkbox" role="switch" id="slackSwitch" checked />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Github -->
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('Admin/img/icons/brands/github.png') }}" alt="github" class="me-3" height="30" />
                                </div>
                                <div class="flex-grow-1 row">
                                    <div class="col-9 mb-sm-0 mb-2">
                                        <h6 class="mb-0">Github</h6>
                                        <small class="text-muted">Manage your Git repositories</small>
                                    </div>
                                    <div class="col-3 text-end">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input float-end" type="checkbox" role="switch" id="githubSwitch" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mailchimp -->
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('Admin/img/icons/brands/mailchimp.png') }}" alt="mailchimp" class="me-3" height="30" />
                                </div>
                                <div class="flex-grow-1 row">
                                    <div class="col-9 mb-sm-0 mb-2">
                                        <h6 class="mb-0">Mailchimp</h6>
                                        <small class="text-muted">Email marketing service</small>
                                    </div>
                                    <div class="col-3 text-end">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input float-end" type="checkbox" role="switch" id="mailchimpSwitch" checked />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Asana -->
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('Admin/img/icons/brands/asana.png') }}" alt="asana" class="me-3" height="30" />
                                </div>
                                <div class="flex-grow-1 row">
                                    <div class="col-9 mb-sm-0 mb-2">
                                        <h6 class="mb-0">Asana</h6>
                                        <small class="text-muted">Communication</small>
                                    </div>
                                    <div class="col-3 text-end">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input float-end" type="checkbox" role="switch" id="asanaSwitch" checked />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Accounts -->
                <div class="col-md-6 col-12">
                    <div class="card">
                        <h5 class="card-header">Social Accounts</h5>
                        <div class="card-body">
                            <p>Display content from social accounts on your site</p>
                            
                            <!-- Facebook -->
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('Admin/img/icons/brands/facebook.png') }}" alt="facebook" class="me-3" height="30" />
                                </div>
                                <div class="flex-grow-1 row">
                                    <div class="col-8 col-sm-7 mb-sm-0 mb-2">
                                        <h6 class="mb-0">Facebook</h6>
                                        <small class="text-muted">Not Connected</small>
                                    </div>
                                    <div class="col-4 col-sm-5 text-end">
                                        <button type="button" class="btn btn-icon btn-outline-secondary">
                                            <i class="bx bx-link-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Twitter -->
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('Admin/img/icons/brands/twitter.png') }}" alt="twitter" class="me-3" height="30" />
                                </div>
                                <div class="flex-grow-1 row">
                                    <div class="col-8 col-sm-7 mb-sm-0 mb-2">
                                        <h6 class="mb-0">Twitter</h6>
                                        <a href="https://twitter.com/YourUsername" target="_blank">@YourUsername</a>
                                    </div>
                                    <div class="col-4 col-sm-5 text-end">
                                        <button type="button" class="btn btn-icon btn-outline-danger">
                                            <i class="bx bx-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Instagram -->
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('Admin/img/icons/brands/instagram.png') }}" alt="instagram" class="me-3" height="30" />
                                </div>
                                <div class="flex-grow-1 row">
                                    <div class="col-8 col-sm-7 mb-sm-0 mb-2">
                                        <h6 class="mb-0">Instagram</h6>
                                        <a href="https://www.instagram.com/yourusername/" target="_blank">@yourusername</a>
                                    </div>
                                    <div class="col-4 col-sm-5 text-end">
                                        <button type="button" class="btn btn-icon btn-outline-danger">
                                            <i class="bx bx-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Dribbble -->
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('Admin/img/icons/brands/dribbble.png') }}" alt="dribbble" class="me-3" height="30" />
                                </div>
                                <div class="flex-grow-1 row">
                                    <div class="col-8 col-sm-7 mb-sm-0 mb-2">
                                        <h6 class="mb-0">Dribbble</h6>
                                        <small class="text-muted">Not Connected</small>
                                    </div>
                                    <div class="col-4 col-sm-5 text-end">
                                        <button type="button" class="btn btn-icon btn-outline-secondary">
                                            <i class="bx bx-link-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Behance -->
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('Admin/img/icons/brands/behance.png') }}" alt="behance" class="me-3" height="30" />
                                </div>
                                <div class="flex-grow-1 row">
                                    <div class="col-8 col-sm-7 mb-sm-0 mb-2">
                                        <h6 class="mb-0">Behance</h6>
                                        <small class="text-muted">Not Connected</small>
                                    </div>
                                    <div class="col-4 col-sm-5 text-end">
                                        <button type="button" class="btn btn-icon btn-outline-secondary">
                                            <i class="bx bx-link-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection