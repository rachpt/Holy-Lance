<?php
/*
 * Holy Lance
 * https://github.com/lincanbin/Holy-Lance
 *
 * Copyright 2016 Canbin Lin (lincanbin@hotmail.com)
 * http://www.94cb.com/
 *
 * Licensed under the MIT License:
 * https://opensource.org/licenses/MIT
 * 
 * A Linux Resource / Performance Monitor based on PHP. 
 */
if (defined('HAS_BEEN_COMPILED') === false) {
	require __DIR__ . '/common.php';
}
header('Content-type: application/json');
check_password();


exec("cat /proc/net/dev | grep \":\" | awk -F ':' '{gsub(\" \", \"\"); if ($2 > 0) print $1}'", $network_cards);
exec("cat /proc/diskstats | awk '{if ($4 > 0) print $3}'", $disk);
$cpu_info = array(
	'cpu_name' => trim(shell_exec('cat /proc/cpuinfo | grep name | cut -f2 -d: | head -1')), // CPU名称
	'cpu_num' => trim(shell_exec('cat /proc/cpuinfo | grep "physical id"| sort | uniq | wc -l')), // CPU个数（X路CPU）
	'cpu_core_num' => trim(shell_exec('cat /proc/cpuinfo | grep "cores" | uniq | awk -F ":" \'{print $2}\'')), // CPU核心数
	'cpu_processor_num' => trim(shell_exec('cat /proc/cpuinfo | grep "processor" | wc -l')), // CPU逻辑处理器个数
	'cpu_frequency' => trim(shell_exec('cat /proc/cpuinfo | grep MHz | uniq | awk -F ":" \'{print $2}\'')), // CPU 频率
);
$all_cpu_info = array_map("get_cpu_info_map", explode("\n\n", trim(shell_exec('cat /proc/cpuinfo'))));
$memory_info = get_mem_info_map(explode("\n", trim(shell_exec('cat /proc/meminfo'))));
$network_info = array();
foreach ($network_cards as $eth) {
	$network_info[$eth]['ip'] = explode("\n", trim(shell_exec("ip addr show " . $eth . " | grep 'inet' | awk '{print $2}' 2> /dev/null; if [[ $? -ne 0 ]]; then ifconfig " . $eth . " | grep 'inet' | sed 's/addr://g' | awk '{print $2}'; fi")));
}
$system_env = array(
	'status' => true,
	'version' => 1,
	'system_name' => trim(shell_exec('cat /etc/*-release | head -n1')),
	'psssword_require' => false,
	'cpu_info' => $cpu_info,
	'cpu' => $all_cpu_info,
	'disk' => $disk,
	'memory' => $memory_info,
	'network' => $network_cards,
	'network_info' => $network_info
);

if (version_compare(PHP_VERSION, '5.4.0') < 0) {
	echo json_encode($system_env);
} else {
	echo json_encode($system_env, JSON_PRETTY_PRINT);
}
?>
