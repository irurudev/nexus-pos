<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS API Documentation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@3/swagger-ui.css">
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin: 0;
            padding: 0;
            background: #fafafa;
        }
        .topbar {
            background-color: #1e1e1e;
            padding: 10px 0;
            color: #fff;
            text-align: center;
        }
        .topbar h1 {
            margin: 0;
            font-size: 1.5em;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <h1>🛒 POS System API Documentation</h1>
    </div>
    <div id="swagger-ui"></div>

    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@3/swagger-ui-bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@3/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            SwaggerUIBundle({
                url: '/api/swagger.json',
                dom_id: '#swagger-ui',
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                layout: "BaseLayout",
                deepLinking: true,
                tryItOutEnabled: true,
                requestInterceptor: (request) => {
                    return request;
                },
                responseInterceptor: (response) => {
                    return response;
                }
            });
        };
    </script>
</body>
</html>
