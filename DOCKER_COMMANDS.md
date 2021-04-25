Get issues statistics for copy/paste:    
```bash
git clone https://github.com/softwaretalks/newsletter softwaretalks_newsletter
cd softwaretalks_newsletter
docker run -it --rm --name issues_statistics_script -v "$PWD":/usr/src/newsletter -w /usr/src/newsletter php:7.4-cli-alpine php issues_statistics.php
```
