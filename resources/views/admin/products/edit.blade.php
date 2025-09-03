<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المنتج</title>
</head>
<body>
    <form action="{{ route('admin.products.update', [$store->id, $product->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <h2>تعديل منتج</h2>

        <label for="name">اسم المنتج:</label>
        <input type="text" id="name" name="name" value="{{ $product->name }}" required>

        <label for="description">الوصف:</label>
        <textarea id="description" name="description">{{ $product->description }}</textarea>

        <label for="price">السعر:</label>
        <input type="number" id="price" name="price" value="{{ $product->price }}" required>

        <label for="quantity">الكمية:</label>
        <input type="number" id="quantity" name="quantity" value="{{ $product->quantity }}" required>

        <label for="images">صور المنتج:</label>
        <input type="file" id="images" name="images[]" multiple>

        <button type="submit">تحديث منتج</button>
    </form>
</body>
</html>
