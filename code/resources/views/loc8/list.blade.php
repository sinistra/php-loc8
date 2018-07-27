<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
    </head>
    <body>
        <ul>
            @foreach ($locs as $loc)
                <li>{{ $loc->MT_LOCID . ' | ' . $loc->NBN_LOCID  . ' | ' . $loc->FORMATTED_ADDRESS_STRING}}</li>
            @endforeach
        </ul>
    </body>
</html>
