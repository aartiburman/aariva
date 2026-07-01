@extends('backend.layouts.app')

@section('title', 'Facebook Login Debug')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Facebook Login Debug Information</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5>Firebase Configuration Status:</h5>
                        <ul>
                            <li><strong>API Key:</strong> {{ $notificationSetting->firebase_api_key ? '✅ Configured' : '❌ Missing' }}</li>
                            <li><strong>Auth Domain:</strong> {{ $notificationSetting->firebase_auth_domain ? '✅ Configured' : '❌ Missing' }}</li>
                            <li><strong>Project ID:</strong> {{ $notificationSetting->firebase_project_id ? '✅ Configured' : '❌ Missing' }}</li>
                            <li><strong>App ID:</strong> {{ $notificationSetting->firebase_app_id ? '✅ Configured' : '❌ Missing' }}</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <h5>⚠️ Important Steps to Complete Facebook Login Setup:</h5>
                        <ol>
                            <li><strong>Firebase Console Setup:</strong>
                                <ul>
                                    <li>Go to <a href="https://console.firebase.google.com" target="_blank">Firebase Console</a></li>
                                    <li>Select your project: <code>{{ $notificationSetting->firebase_project_id ?? 'YOUR_PROJECT_ID' }}</code></li>
                                    <li>Go to Authentication → Sign-in method</li>
                                    <li>Enable Facebook sign-in provider</li>
                                    <li>Add your Facebook App ID: <code>{{ env('FACEBOOK_APP_ID') }}</code></li>
                                    <li>Add your Facebook App Secret: <code>{{ env('FACEBOOK_APP_SECRET') }}</code></li>
                                    <li>Save the changes</li>
                                </ul>
                            </li>
                            <li><strong>Meta Developer Console:</strong>
                                <ul>
                                    <li>Go to <a href="https://developers.facebook.com" target="_blank">Meta for Developers</a></li>
                                    <li>Select your app</li>
                                    <li>Go to Facebook Login → Settings</li>
                                    <li>Add the redirect URI: <code>https://{{ request()->getHost() }}/__/auth/handler</code></li>
                                    <li>Also add: <code>https://aariva-auth.firebaseapp.com/__/auth/handler</code></li>
                                </ul>
                            </li>
                            <li><strong>Test the Login:</strong>
                                <ul>
                                    <li>Clear browser cache and cookies</li>
                                    <li>Try Facebook login again</li>
                                    <li>Check browser console for errors</li>
                                    <li>Check Laravel logs for backend errors</li>
                                </ul>
                            </li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Environment Variables:</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>FACEBOOK_APP_ID:</strong></td>
                                    <td>{{ env('FACEBOOK_APP_ID') ? '✅ Set' : '❌ Not Set' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>FACEBOOK_APP_SECRET:</strong></td>
                                    <td>{{ env('FACEBOOK_APP_SECRET') ? '✅ Set' : '❌ Not Set' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Test Facebook Login:</h5>
                            <button class="btn btn-outline-primary" onclick="testFacebookLogin()">
                                <i class="fab fa-facebook"></i> Test Facebook Login
                            </button>   
                            <div id="test-result" class="mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testFacebookLogin() {
    const resultDiv = document.getElementById('test-result');
    resultDiv.innerHTML = '<div class="alert alert-info">Testing Facebook login...</div>';

    // Import Firebase modules
    import('https://www.gstatic.com/firebasejs/12.10.0/firebase-app.js').then(({ initializeApp }) => {
        import('https://www.gstatic.com/firebasejs/12.10.0/firebase-auth.js').then(({ getAuth, FacebookAuthProvider, signInWithPopup }) => {
            // Firebase config
            const firebaseConfig = {
                apiKey: "{{ $notificationSetting->firebase_api_key ?? '' }}",
                authDomain: "{{ $notificationSetting->firebase_auth_domain ?? '' }}",
                projectId: "{{ $notificationSetting->firebase_project_id ?? '' }}",
                storageBucket: "{{ $notificationSetting->firebase_storage_bucket ?? '' }}",
                messagingSenderId: "{{ $notificationSetting->firebase_messaging_sender_id ?? '' }}",
                appId: "{{ $notificationSetting->firebase_app_id ?? '' }}",
                measurementId: "{{ $notificationSetting->measurementId ?? '' }}",
            };

            try {
                const app = initializeApp(firebaseConfig);
                const auth = getAuth(app);
                const facebookProvider = new FacebookAuthProvider();

                resultDiv.innerHTML = '<div class="alert alert-success">Firebase initialized successfully. Attempting Facebook login...</div>';

                signInWithPopup(auth, facebookProvider).then((result) => {
                    resultDiv.innerHTML = '<div class="alert alert-success">Facebook login successful! User: ' + result.user.displayName + '</div>';
                }).catch((error) => {
                    let errorMessage = '';
                    if (error.code === 'auth/operation-not-allowed') {
                        errorMessage = 'Facebook login is not enabled in Firebase Console. Please enable it and add your App ID/Secret.';
                    } else if (error.code === 'auth/invalid-credential') {
                        errorMessage = 'Invalid Facebook App ID or Secret in Firebase Console.';
                    } else {
                        errorMessage = error.message;
                    }
                    resultDiv.innerHTML = '<div class="alert alert-danger">Error: ' + errorMessage + '<br>Code: ' + error.code + '</div>';
                });
            } catch (error) {
                resultDiv.innerHTML = '<div class="alert alert-danger">Firebase initialization failed: ' + error.message + '</div>';
            }
        });
    });
}
</script>
@endsection