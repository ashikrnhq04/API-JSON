<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MockerJSON - Free Mock Data REST API</title>
    <meta name="description"
        content="Free mock data JSON API for testing and development. Get realistic product and blog post data for your projects with simple REST endpoints.">
    <meta name="keywords" content="mock data, JSON API, REST API, fake data, testing, development, products, posts">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png">
    <style>
    * {
        padding: 0;
        margin: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #333;
        min-height: 100vh;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .header {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        color: white;
        padding: 60px 0;
        text-align: center;
        margin-bottom: 40px;
    }

    .header h1 {
        font-size: 3rem;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .header .subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-bottom: 20px;
    }

    .status-badges {
        display: flex;
        justify-content: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        backdrop-filter: blur(5px);
    }

    .content {
        background: white;
        margin: 0 auto 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .section {
        padding: 40px;
        border-bottom: 1px solid #eee;
    }

    .section:last-child {
        border-bottom: none;
    }

    .section h2 {
        color: #333;
        margin-bottom: 20px;
        font-size: 2rem;
        border-left: 4px solid #667eea;
        padding-left: 15px;
    }

    .section h3 {
        color: #555;
        margin: 25px 0 15px;
        font-size: 1.3rem;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin: 30px 0;
    }

    .feature-card {
        background: #f8f9fa;
        padding: 25px;
        border-radius: 10px;
        border-left: 4px solid #667eea;
        transition: transform 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .feature-card h4 {
        color: #333;
        margin-bottom: 10px;
        font-size: 1.1rem;
    }

    .endpoints-list {
        display: grid;
        gap: 20px;
        margin: 20px 0;
    }

    .endpoint {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        border-left: 4px solid #28a745;
    }

    .endpoint-method {
        background: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: bold;
        margin-right: 10px;
    }

    .endpoint-url {
        font-family: 'Courier New', monospace;
        background: #e9ecef;
        padding: 8px 12px;
        border-radius: 5px;
        margin: 10px 0;
        font-size: 1rem;
    }

    .try-button {
        background: #667eea;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        display: inline-block;
        margin-top: 10px;
        transition: background 0.3s ease;
    }

    .try-button:hover {
        background: #5a67d8;
        text-decoration: none;
    }

    .code-block {
        background: #2d3748;
        color: #e2e8f0;
        padding: 20px;
        border-radius: 8px;
        overflow-x: auto;
        margin: 15px 0;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .code-title {
        background: #4a5568;
        color: white;
        padding: 8px 15px;
        margin: 20px 0 0;
        border-radius: 8px 8px 0 0;
        font-size: 0.9rem;
        font-weight: bold;
    }

    .code-title+.code-block {
        margin-top: 0;
        border-radius: 0 0 8px 8px;
    }

    .params-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }

    .params-table th,
    .params-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .params-table th {
        background: #f8f9fa;
        font-weight: 600;
    }

    .params-table code {
        background: #e9ecef;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.9rem;
    }

    .footer {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        color: #333;
        text-align: center;
        padding: 40px 0;
        margin-top: 40px;
    }

    .footer a {
        color: #666;
        text-decoration: none;
        font-weight: 600;
    }

    .footer a:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .header h1 {
            font-size: 2rem;
        }

        .section {
            padding: 20px;
        }

        .status-badges {
            flex-direction: column;
            align-items: center;
        }
    }
    </style>
</head>

<body>
    <header class="header">
        <div class="container">
            <h1>MockerJSON</h1>
            <p class="subtitle">Free Mock Data REST API for Developers</p>
            <p>High-quality, realistic mock data for testing and development</p>
            <div class="status-badges">
                <span class="badge">✅ Free Forever</span>
                <span class="badge">⚡ Rate Limited (1000/hour)</span>
                <span class="badge">🔧 No Setup Required</span>
                <span class="badge">📱 CORS Enabled</span>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="content">
            <!-- Quick Start Section -->
            <section class="section">
                <h2>🚀 Quick Start</h2>
                <p>Get started immediately with our RESTful API. No authentication required!</p>

                <div class="code-title">Example Request</div>
                <div class="code-block">fetch('https://mockerjson.xyz/api/v1/products?limit=5')
                    .then(response => response.json())
                    .then(data => console.log(data));</div>

                <div class="code-title">Example Response</div>
                <div class="code-block">{
                    "status": "success",
                    "message": "Products retrieved successfully",
                    "pagination": {
                    "total": 119,
                    "limit": 5,
                    "offset": 0,
                    "hasMore": true
                    },
                    "data": [
                    {
                    "id": 1,
                    "title": "Wireless Bluetooth Headphones",
                    "description": "High-quality wireless headphones with noise cancellation",
                    "price": 99.99,
                    "image": "https://placehold.co/400x300/3498DB/FFFFFF",
                    "url": "wireless-bluetooth-headphones"
                    }
                    ]
                    }</div>
            </section>

            <!-- Features Section -->
            <section class="section">
                <h2>✨ Features</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <h4>🎯 Realistic Data</h4>
                        <p>High-quality, realistic product and blog post data with proper relationships and consistent
                            formatting.</p>
                    </div>
                    <div class="feature-card">
                        <h4>⚡ Fast & Reliable</h4>
                        <p>Optimized responses with consistent performance. Perfect for development and testing
                            environments.</p>
                    </div>
                    <div class="feature-card">
                        <h4>🔄 RESTful Design</h4>
                        <p>Standard HTTP methods and status codes. Follows REST API best practices and conventions.</p>
                    </div>
                    <div class="feature-card">
                        <h4>📊 Pagination Support</h4>
                        <p>Built-in pagination with limit, offset, and metadata. Control exactly how much data you need.
                        </p>
                    </div>
                    <div class="feature-card">
                        <h4>🌐 CORS Enabled</h4>
                        <p>Cross-origin requests supported. Use directly from your frontend applications without proxy.
                        </p>
                    </div>
                    <div class="feature-card">
                        <h4>🆓 Completely Free</h4>
                        <p>No API keys, no registration required. Rate limiting is active to ensure fair usage and
                            optimal performance for everyone.</p>
                    </div>
                </div>
            </section>

            <!-- Rate Limiting Section -->
            <section class="section">
                <h2>⚡ Rate Limiting</h2>
                <p>Fair usage limits are in place to ensure optimal performance for all users:</p>

                <div class="features-grid">
                    <div class="feature-card">
                        <h4>🕐 Hourly Limits</h4>
                        <p><strong>1000 requests per hour</strong> for API endpoints. Perfect for development and
                            testing needs.</p>
                    </div>
                    <div class="feature-card">
                        <h4>🚀 Burst Protection</h4>
                        <p><strong>50 requests per minute</strong> burst limit prevents abuse while allowing normal
                            usage patterns.</p>
                    </div>
                    <div class="feature-card">
                        <h4>📊 Rate Limit Headers</h4>
                        <p>Every response includes headers showing your current usage, remaining requests, and reset
                            time.</p>
                    </div>
                    <div class="feature-card">
                        <h4>🏠 Localhost Unlimited</h4>
                        <p>No limits for localhost development. Test freely on your local machine without restrictions.
                        </p>
                    </div>
                </div>

                <h3>Rate Limit Headers</h3>
                <p>All API responses include these headers to help you manage your usage:</p>

                <div class="code-title">Response Headers</div>
                <div class="code-block">X-RateLimit-Limit: 1000
                    X-RateLimit-Remaining: 847
                    X-RateLimit-Reset: 1753892115
                    Retry-After: 45 (only when rate limited)</div>

                <table class="params-table">
                    <thead>
                        <tr>
                            <th>Header</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>X-RateLimit-Limit</code></td>
                            <td>Your total hourly request allowance</td>
                        </tr>
                        <tr>
                            <td><code>X-RateLimit-Remaining</code></td>
                            <td>Number of requests remaining in current window</td>
                        </tr>
                        <tr>
                            <td><code>X-RateLimit-Reset</code></td>
                            <td>Unix timestamp when your allowance resets</td>
                        </tr>
                        <tr>
                            <td><code>Retry-After</code></td>
                            <td>Seconds to wait before retrying (when rate limited)</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- API Endpoints Section -->
            <section class="section">
                <h2>📡 API Endpoints</h2>

                <div class="endpoints-list">
                    <div class="endpoint">
                        <h3><span class="endpoint-method">GET</span>Products Endpoint</h3>
                        <div class="endpoint-url">https://mockerjson.xyz/api/v1/products</div>
                        <p>Retrieve a list of mock products with details like title, description, price, and images.</p>
                        <a href="/api/v1/products" class="try-button" target="_blank">Try it live</a>
                    </div>

                    <div class="endpoint">
                        <h3><span class="endpoint-method">GET</span>Single Product</h3>
                        <div class="endpoint-url">https://mockerjson.xyz/api/v1/products/{id|slug}</div>
                        <p>Get a specific product by ID (number) or URL slug (string).</p>
                        <a href="/api/v1/products/1" class="try-button" target="_blank">Try with ID</a>
                        <a href="/api/v1/products/updated-post-title" class="try-button" target="_blank">Try
                            with Slug</a>
                    </div>

                    <div class="endpoint">
                        <h3><span class="endpoint-method">GET</span>Posts Endpoint</h3>
                        <div class="endpoint-url">https://mockerjson.xyz/api/v1/posts</div>
                        <p>Retrieve mock blog posts with titles, content, images, and metadata.</p>
                        <a href="/api/v1/posts" class="try-button" target="_blank">Try it live</a>
                    </div>

                    <div class="endpoint">
                        <h3><span class="endpoint-method">GET</span>Single Post</h3>
                        <div class="endpoint-url">https://mockerjson.xyz/api/v1/posts/{id|slug}</div>
                        <p>Get a specific blog post by ID (number) or URL slug (string).</p>
                        <a href="/api/v1/posts/1" class="try-button" target="_blank">Try with ID</a>
                        <a href="/api/v1/posts/updated-post-title" class="try-button" target="_blank">Try
                            with Slug</a>
                    </div>
                </div>
            </section>

            <!-- Query Parameters Section -->
            <section class="section">
                <h2>⚙️ Query Parameters</h2>
                <p>Customize your requests with these optional parameters:</p>

                <table class="params-table">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Type</th>
                            <th>Default</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>limit</code></td>
                            <td>integer</td>
                            <td>10</td>
                            <td>Number of items to return (1-100)</td>
                        </tr>
                        <tr>
                            <td><code>offset</code></td>
                            <td>integer</td>
                            <td>0</td>
                            <td>Number of items to skip</td>
                        </tr>
                    </tbody>
                </table>
            </section>


            <footer class="footer">
                <div class="container">
                    <p>MockerJSON API is a free service for developers</p>
                    <p>Built with <span style="color: red">&#10084;</span> by <a
                            href="mailto:ashikrn.hq04@gmail.com">Ashik</a></p>
                </div>
            </footer>
</body>

</html>