<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock data JSON API</title>
    <style>
    * {
        padding: 0;
        margin: 0;
    }

    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        background-color: #f9f9f9;
        color: #333;
    }

    h1 {
        color: #333;
    }

    a {
        color: #007BFF;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    pre {
        background-color: #f4f4f4;
        padding: 10px;
        border-radius: 5px;
    }

    ul {
        padding-left: 0;
        list-style-position: inside;
    }

    li {
        margin-bottom: 10px;
    }

    .header {
        background-color: #31343c;
        color: white;
        padding: 50px 20px;
        margin-bottom: 20px;
    }

    footer {
        background-color: #31343c;
        padding: 20px;
        text-align: center;
        color: #7c7373;

        a {

            color: #fff;
        }
    }

    .header h1 {
        max-width: 800px;
        margin: 0;
        color: #fff;
    }

    .content {
        padding: 20px;
        min-height: 100svh;
    }
    </style>
</head>

<body>
    <header class="header">
        <h1>This is a simple API's that provides mock data in JSON format. Inspired by <a style="color: white;"
                href=" https://jsonplaceholder.typicode.com/">jsonplaceholder.com</a></h1>
    </header>
    <div class="content">
        <p>All the REST methods are supported except PUT. Other than GET, all the operations are dummy.</p>
        <pre>Exmaple: <code>GET /api/v1/products</code></pre>

        <h3 style="margin: 20px 0px">Available endpoints:</h3>
        <ul>
            <li><a href="/api/v1/products">/api/v1/products</a> - Get a list of products</li>
            <li><a href="/api/v1/products/1">/api/v1/products/{id|slug}</a> - Get a single product by ID or Slug
            </li>
            <li><a href="/api/v1/posts">/api/v1/posts</a> - Get a list of posts</li>
            <li><a href="/api/v1/posts/{id}">/api/v1/posts/{id|slug}</a> - Get a single post by ID or Slug</li>
        </ul>
    </div>

    <footer>
        Mock data endpoints. Developed by <a href="mailto:ashikrn.hq04@gmail.com">Ashik</a>

    </footer>
</body>

</html>