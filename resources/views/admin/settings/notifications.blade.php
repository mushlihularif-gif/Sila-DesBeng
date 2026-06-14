@extends('admin.dashboard.index')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Account Settings /</span> Notifications
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
                    <a class="nav-link active" href="javascript:void(0);">
                        <i class="bx bx-bell me-1"></i> Notifications
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.settings.connections') }}">
                        <i class="bx bx-link-alt me-1"></i> Connections
                    </a>
                </li>
            </ul>

            <div class="card">
                <!-- Notifications -->
                <h5 class="card-header">Notification Preferences</h5>
                <div class="card-body">
                    <span>We need permission from your browser to show notifications.
                        <span class="notificationRequest"><strong>Request Permission</strong></span>
                    </span>
                    <div class="error"></div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-borderless border-bottom">
                        <thead>
                            <tr>
                                <th class="text-nowrap">Type</th>
                                <th class="text-nowrap text-center">✉️ Email</th>
                                <th class="text-nowrap text-center">🖥 Browser</th>
                                <th class="text-nowrap text-center">👩🏻‍💻 App</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-nowrap">New for you</td>
                                <td>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="emailCheck1" checked />
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="browserCheck1" checked />
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="appCheck1" checked />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Account activity</td>
                                <td>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="emailCheck2" checked />
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="browserCheck2" checked />
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="appCheck2" checked />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">A new browser used to sign in</td>
                                <td>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="emailCheck3" checked />
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="browserCheck3" checked />
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="appCheck3" />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">A new device is linked</td>
                                <td>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="emailCheck4" checked />
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="browserCheck4" />
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="appCheck4" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-body">
                    <h6>When should we send you notifications?</h6>
                    <form action="{{ route('admin.settings.notifications.update') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-sm-6">
                                <select id="sendNotification" class="form-select" name="sendNotification">
                                    <option selected>Only when I'm online</option>
                                    <option>Anytime</option>
                                </select>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary me-2">Save changes</button>
                                <button type="reset" class="btn btn-outline-secondary">Discard</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /Notifications -->
            </div>
        </div>
    </div>
</div>
@endsection