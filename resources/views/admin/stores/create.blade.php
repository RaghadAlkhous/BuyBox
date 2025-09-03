<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة متجر جديد</title>
</head>
<body>
    <form action="{{ route('admin.stores.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <h2>إضافة متجر جديد</h2>

        <label for="name">اسم المتجر:</label>
        <input type="text" id="name" name="name" required>

        <label for="description">الوصف:</label>
        <textarea id="description" name="description"></textarea>

        <label for="store_image">صورة المتجر:</label>
        <input type="file" id="store_image" name="store_image">

        <button type="submit">إضافة متجر</button>
    </form>
</body>
</html>
