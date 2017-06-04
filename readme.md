# MRC DOC

## You need

 - Redis
 - MySQL
 - swoole
 
## Nginx Config

Front end:

    server {
        
        ...
        
        location / {
            try_files $uri $uri/ /index.html;
        }
        
        ...
        
    }

Back end:

    server {
    
        ...
        
        add_header 'Access-Control-Allow-Origin' "$http_origin";
        add_header 'Access-Control-Allow-Credentials' "true";
        add_header 'Access-Control-Max-Age' 2592000;
        add_header 'Access-Control-Allow-Methods' 'GET,PUT,POST, OPTIONS, DELETE,PATCH';
        add_header 'Access-Control-Allow-Headers' 'X-OAUTH-TOKEN, token, accept, content-type';
        if ($request_method = 'OPTIONS') {
            return 200;
        }
        
        ...
        
    }
    
## Before start

    cd .../MRCDOC_API
    php socket.php &
    
    