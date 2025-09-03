<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المتاجر</title>
</head>
<body>
    <h1>إدارة المتاجر</h1>
    <a href="{{ route('admin.stores.create') }}">إضافة متجر جديد</a>

    <table>
        <thead>
            <tr>
                <th>اسم المتجر</th>
                <th>الوصف</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stores as $store)
            <tr>
                <td>{{ $store->name }}</td>
                <td>{{ $store->description }}</td>
                <td>
                    <a href="{{ route('admin.stores.edit', $store->id) }}">تعديل</a>
                    <form action="{{ route('admin.stores.destroy', $store->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
