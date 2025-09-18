# track.php
track.php is a simple frontend and easy-to-use api for Track24 (both [.net](https://track24.net) and [.ru](https://track24.ru))

## Setup
Dump tracking url, key and cp hash from [track24.net](https://track24.net) and [track24.ru](https://track24.ru) using this script:
```JavaScript
alert(`$${(new URL(window.location.href)).hostname.split('.').splice(-1).join('.').toLowerCase()}_cp_hash = "${cp}";
$${(new URL(window.location.href)).hostname.split('.').splice(-1).join('.').toLowerCase()}_tracking_key = "${(typeof trackingKey !== "undefined" ? trackingKey : key)}";
$${(new URL(window.location.href)).hostname.split('.').splice(-1).join('.').toUpperCase()}_TRACKING_URL = "${window.location.protocol + "//" + window.location.hostname + trackingUrl}";`);
```
then fill that to ```track.php```

Also, you can update service (only for [.ru](https://track24.ru)) and language lists in the ```index.php``` using this script:
```JavaScript
alert(`$service_list = ${"[" + [...document.querySelectorAll('[service-code]')].map(a => `"${a.getAttribute('service-code')}"`).join(",") + "]"};
$language_list = [${availibleLanguageArray.map(item => `"${item}"`).join(", ")}];`);
```

## API Usage
```
accepted parameters:
	code - string, tracking code, required
	service - string, delivery service (only for .ru)
	language - string, status localization language
	domain - string, "net" or "ru"
```

# look at this silly screenshot xd
<img width="697" height="836" alt="scr1" src="https://github.com/user-attachments/assets/3d50a02b-4eee-4c86-bde5-075da53fa505" />
