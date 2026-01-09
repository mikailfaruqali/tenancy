<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Workspace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            width: 100%;
        }

        body {
            min-height: 100vh;
            background: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            padding: 1.5rem;
        }

        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 30% 20%, rgba(34, 197, 94, 0.06) 0%, transparent 50%),
                radial-gradient(circle at 70% 80%, rgba(59, 130, 246, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .container {
            text-align: center;
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 380px;
        }

        h1 {
            font-size: 1.625rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }

        .subtitle {
            color: #64748b;
            font-size: 0.875rem;
            line-height: 1.6;
            margin-bottom: 1.75rem;
        }

        .form-group {
            margin-bottom: 1rem;
            text-align: left;
        }

        label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.375rem;
        }

        input {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: white;
            color: #1e293b;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        input:focus {
            outline: none;
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }

        input:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        input.has-error {
            border-color: #ef4444;
        }

        input.has-error:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        button {
            width: 100%;
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: opacity 0.15s, transform 0.15s;
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.25);
        }

        button:hover:not(:disabled) {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .error-box {
            display: none;
            margin-bottom: 1.25rem;
            padding: 0.75rem 1rem;
            font-size: 0.8125rem;
            text-align: left;
            border-radius: 10px;
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            word-wrap: break-word;
            overflow-wrap: break-word;
            overflow: hidden;
            max-height: 120px;
            overflow-y: auto;
        }

        .loading-box {
            display: none;
            margin-bottom: 1.25rem;
            padding: 0.75rem 1rem;
            font-size: 0.8125rem;
            border-radius: 10px;
            background: white;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .spinner {
            width: 14px;
            height: 14px;
            border: 2px solid #e2e8f0;
            border-top-color: #22c55e;
            border-radius: 50%;
            display: inline-block;
            animation: spin 0.8s linear infinite;
            margin-right: 0.5rem;
            vertical-align: middle;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="background"></div>

    <div class="container">
        <h1>Create Workspace</h1>

        <p class="subtitle">Set up your workspace with a custom domain.</p>

        <div class="error-box"></div>

        <div class="loading-box">
            <span class="spinner"></span>
            Creating workspace…
        </div>

        <form id="workspace-form">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="form-group">
                <label for="domain">Domain</label>
                <input type="text" id="domain" name="domain" placeholder="my-company" autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••">
            </div>

            <button type="submit">Create</button>
        </form>
    </div>

    <script>
        (() => {
            const formElement = document.getElementById('workspace-form');
            const errorBoxElement = document.querySelector('.error-box');
            const loadingBoxElement = document.querySelector('.loading-box');
            const submitButtonElement = formElement.querySelector('button');
            const domainInputElement = document.getElementById('domain');
            const passwordInputElement = document.getElementById('password');
            const tokenInputElement = formElement.querySelector('[name="_token"]');

            const showLoading = () => {
                loadingBoxElement.style.display = 'block';
                submitButtonElement.disabled = true;
                domainInputElement.disabled = true;
                passwordInputElement.disabled = true;
            };

            const hideLoading = () => {
                loadingBoxElement.style.display = 'none';
                submitButtonElement.disabled = false;
                domainInputElement.disabled = false;
                passwordInputElement.disabled = false;
            };

            const clearErrors = () => {
                errorBoxElement.style.display = 'none';
                errorBoxElement.textContent = '';
                domainInputElement.classList.remove('has-error');
                passwordInputElement.classList.remove('has-error');
            };

            const showError = (message) => {
                errorBoxElement.textContent = message;
                errorBoxElement.style.display = 'block';
            };

            const markFieldError = (fieldName) => {
                if (fieldName === 'domain') {
                    domainInputElement.classList.add('has-error');
                }

                if (fieldName === 'password') {
                    passwordInputElement.classList.add('has-error');
                }
            };

            const handleValidationErrors = (errors) => {
                const fieldNames = Object.keys(errors);

                fieldNames.forEach((fieldName) => {
                    markFieldError(fieldName);
                });

                const firstFieldErrors = errors[fieldNames[0]];

                if (firstFieldErrors && firstFieldErrors[0]) {
                    showError(firstFieldErrors[0]);
                }
            };

            const handleSubmit = async (event) => {
                event.preventDefault();

                clearErrors();
                showLoading();

                const formData = new FormData();
                formData.append('_token', tokenInputElement.value);
                formData.append('domain', domainInputElement.value);
                formData.append('password', passwordInputElement.value);

                const response = await fetch("{{ route('tenancy.create') }}", {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const responseData = await response.json();

                if (responseData.redirect) {
                    window.open(`https://${responseData.redirect}`, '_blank');
                    return;
                }

                hideLoading();

                if (responseData.errors) {
                    handleValidationErrors(responseData.errors);
                    return;
                }

                showError(responseData.message || 'Something went wrong.');
            };

            formElement.addEventListener('submit', handleSubmit);
        })();
    </script>
</body>

</html>
