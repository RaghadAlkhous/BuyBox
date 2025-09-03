<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المتجر</title>
</head>
<body>
    <form action="{{ route('admin.stores.update', $store->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <h2>تعديل متجر</h2>

        <label for="name">اسم المتجر:</label>
        <input type="text" id="name" name="name" value="{{ $store->name }}" required>

        <label for="description">الوصف:</label>
        <textarea id="description" name="description">{{ $store->description }}</textarea>

        <label for="store_image">صورة المتجر:</label>
        <input type="file" id="store_image" name="store_image">

        <button type="submit">تحديث</button>
    </form>
</body>
</html>
