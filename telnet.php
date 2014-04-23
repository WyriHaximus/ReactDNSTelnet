<?php

require './vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();

$socket = new \React\Socket\Server($loop);
$socket->listen(13378, '0.0.0.0');

$dnsResolverFactory = new \React\Dns\Resolver\Factory();
$dnsResolver = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$socket->on('connection', function(\React\Socket\Connection $conn) use ($dnsResolver) {
$buffer = '';
$conn->on('data', function($data, $conn) use ($dnsResolver, &$buffer) {
	$buffer .= $data;
	if (strpos($buffer, PHP_EOL) !== false) {
		$hostnames = explode(PHP_EOL, $buffer);
		$buffer = array_pop($hostnames);
		foreach ($hostnames as $hostname) {
			$hostname = trim($hostname);
			$dnsResolver->resolve($hostname)->then(function($ip) use ($conn, $hostname) {
				$conn->write($hostname . ': ' . $ip . PHP_EOL);
			});
		}
	}
});
        $conn->write('Hello state your resolve' . PHP_EOL);
});

$loop->run();
