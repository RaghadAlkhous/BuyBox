<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة منتج جديد</title>
</head>
<body>
    <form action="{{ route('admin.products.store', $store->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <h2>إضافة منتج جديد</h2>

        <label for="name">اسم المنتج:</label>
        <input type="text" id="name" name="name" required>

        <label for="description">الوصف:</label>
        <textarea id="description" name="description"></textarea>

        <label for="price">السعر:</label>
        <input type="number" id="price" name="price" required>

        <label for="quantity">الكمية:</label>
        <input type="number" id="quantity" name="quantity" required>

        <label for="images">صور المنتج:</label>
        <input type="file" id="images" name="images[]" multiple>

        <button type="submit">إضافة منتج</button>
    </form>
</body>
</html>
