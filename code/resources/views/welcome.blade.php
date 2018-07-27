<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
    </head>
    <body>
        <ul>
            @foreach ($tasks as $task)
                <li>{{ $task->body }}</li>
            @endforeach
        </ul>
    </body>
</html>
