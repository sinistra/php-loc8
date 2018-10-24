<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
</head>
<body>
<ul>
    @foreach ($locs as $loc)
        <li>{{ $loc->id . ' | ' . $loc->nbn_locid  . ' | ' . $loc->formatted_address_string}}</li>
    @endforeach
</ul>
</body>
</html>
