<VirtualHost *:80>
    ServerName {{ $domain_without_www }}
    ServerAlias  www.{{ $domain_without_www }}
    DocumentRoot /home/{{ $username }}/public_html
    <Directory "/home/{{ $username }}/public_html">
    Options +SymLinksIfOwnerMatch -Indexes
    AllowOverride All
    </Directory>
    ServerAdmin {{ $username }}@localhost
    UseCanonicalName Off
    DirectoryIndex index.php index.php7 index.php5 index.perl index.pl index.plx index.ppl index.cgi index.jsp index.jp index.phtml index.shtml index.xhtml index.html index.htm index.js

    SuexecUserGroup {{ $username }} {{ $username }}
    <FilesMatch ".php$">
            SetHandler "proxy:unix:/etc/endurance/configs/php/{{$php_version}}/{{ $domain_without_www }}.sock|fcgi://localhost/"
    </FilesMatch>
    LogLevel warn
    ErrorLog /home/{{ $username }}/apache_error.log
    CustomLog /home/{{ $username }}/apache_access.log combined
</VirtualHost>
<VirtualHost *:80>

    ServerAlias endurance.{{ $domain_without_www }}
    DocumentRoot /home/endurance/public_html
    <Directory "/home/endurance/public_html">
      Options +SymLinksIfOwnerMatch -Indexes
      AllowOverride All
    </Directory>
    ServerAdmin webmaster@localhost
    UseCanonicalName Off
    DirectoryIndex index.php index.php7 index.php5 index.perl index.pl index.plx index.ppl index.cgi index.jsp index.jp index.phtml index.shtml index.xhtml index.html index.htm index.js

    SuexecUserGroup endurance endurance

    Alias /endurance/ "/var/www/cgi-bin/endurance/"
    <Directory "/var/www/cgi-bin/endurance">
      Options +ExecCGI
      SetHandler cgi-script
    </Directory>

    <FilesMatch ".php$">
           SetHandler "proxy:unix:/etc/endurance/configs/php/php80-endurance-fpm/endurance.sock|fcgi://localhost/"
     </FilesMatch>

</VirtualHost>

<VirtualHost *:80>

    ServerAlias rover.{{ $domain_without_www }}
    DocumentRoot /home/rover/public_html
    <Directory "/home/rover/public_html">
      Options +SymLinksIfOwnerMatch -Indexes
      AllowOverride All
    </Directory>
    ServerAdmin webmaster@localhost
    UseCanonicalName Off
    DirectoryIndex index.php index.php7 index.php5 index.perl index.pl index.plx index.ppl index.cgi index.jsp index.jp index.phtml index.shtml index.xhtml index.html index.htm index.js
    SuexecUserGroup rover rover
    <FilesMatch ".php$">
           SetHandler "proxy:unix:/etc/endurance/configs/php/php80-endurance-fpm/rover.sock|fcgi://localhost/"
     </FilesMatch>
</VirtualHost>


<VirtualHost *:80>

    ServerAlias mail.{{ $domain_without_www }}
    DocumentRoot /etc/endurance/current/roundcube
    <Directory "/etc/endurance/current/roundcube">
      Options +SymLinksIfOwnerMatch -Indexes
      AllowOverride All
    </Directory>
    ServerAdmin webmaster@localhost
    UseCanonicalName Off
    DirectoryIndex index.php index.php7 index.php5 index.perl index.pl index.plx index.ppl index.cgi index.jsp index.jp index.phtml index.shtml index.xhtml index.html index.htm index.js

    SuexecUserGroup endurance endurance

    Alias /endurance/ "/var/www/cgi-bin/endurance/"
    <Directory "/var/www/cgi-bin/endurance">
      Options +ExecCGI
      SetHandler cgi-script
    </Directory>

    <FilesMatch ".php$">
           SetHandler "proxy:unix:/etc/endurance/configs/php/php80-endurance-fpm/endurance.sock|fcgi://localhost/"
     </FilesMatch>


</VirtualHost>
