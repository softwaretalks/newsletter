Get issues statistics for copy/paste:    
```bash
docker run -it --rm --name issues_statistics_script -v "$PWD":/usr/src/newsletter -w /usr/src/newsletter php:7.4-cli-alpine php issues_statistics.php
```
