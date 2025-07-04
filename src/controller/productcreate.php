<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form method="POST" action="/api/v1/products">
        <label for="title">Product Title
            <input type="text" name="title" id="title">
        </label>
        <label for="category">Category
            <input type="text" name="category" id="category">
        </label>
        <label for="description">
            Product Description
            <textarea name="description" id="description"></textarea>
        </label>
        <input type="submit" value="Submit">
    </form>
</body>

</html>