<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Free Mock data JSON API</title>
    <meta name="description"
        content="Free mock data JSON API for testing and development purposes. This API provides a set of endpoints to retrieve mock data in JSON format.">
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
        <h1>Mock data JSON API</h1>
        <p>Mock data API for testing and development purposes. This API provides a set of endpoints to retrieve mock
            data in JSON format.</p>
    </header>
    <div class="content">
        <h3 style="margin: 20px 0px">Available endpoints:</h3>
        <ul>
            <li><a href="/api/v1/products">/api/v1/products</a> - Get a list of products</li>
            <li><a href="/api/v1/products/1">/api/v1/products/{id|slug}</a> - Get a single product by ID or Slug
            </li>
            <li><a href="/api/v1/posts">/api/v1/posts</a> - Get a list of posts</li>
            <li><a href="/api/v1/posts/1">/api/v1/posts/{id|slug}</a> - Get a single post by ID or Slug</li>
        </ul>
    </div>

    <footer>
        <p>Mock data API is a free service provided by <a href="https://mockerjson.xyz">MockerJSON</a>.</p>
        <p>Developed with <span style="color: red">&#10084;</span> by <a href="mailto:ashikrn.hq04@gmail.com">Ashik</a>
        </p>
    </footer>
</body>

</html>