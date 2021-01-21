[{{ $username}}]
user = {{ $username}}
group = {{ $username}}
listen = /etc/endurance/configs/php/php74/{{ $domain_without_www }}.sock
listen.owner = {{ $apacheuser}}
listen.group = {{ $apachegroup}}
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3