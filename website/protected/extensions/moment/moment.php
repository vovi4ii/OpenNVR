<?php

/*
conf_file - это id камеры, тут надо бы убедиться в том, что юниксовая система поймет имя файла такое как id
Живое видео от сервера:

rtmp://<server_ip>:1935/<cam_id>
Записанное видео от сервера

rtmp://127.0.0.1:1935/nvr/sony?start=1368828341
where start — unixtime of begin playing position from 00:00 1 Jan 1970 in UTC
sony - name of stream

http://127.0.0.1:8082/mod_nvr_admin/rec_on?stream=sony&seq=1 - turn on record
http://127.0.0.1:8082/mod_nvr_admin/rec_off?stream=sony&seq=1 - turn off record

add channel
http://192.168.10.21:8080/admin/add_channel?conf_file=sony0&title=Sony0&uri=rtsp://192.168.10.21:8092/test0.sdp&quota_id=1&subquota_id=1

modify channel
http://192.168.10.21:8080/admin/add_channel?conf_file=sony0&title=Sony0&uri=rtsp://192.168.10.21:8092/test0.sdp&update&quota_id=1&subquota_id=1

remove channel
http://192.168.10.21:8080/admin/remove_channel?conf_file=sony0

add quota
http://192.168.10.21:8080/admin/add_quota?id=1&size=2048000

update quota
http://192.168.10.21:8080/admin/update_quota?id=1&size=2048000

remove quota
http://192.168.10.21:8080/admin/remove_quota?id=1

add subquota
http://192.168.10.21:8080/admin/add_subquota?id=1&idSub=1&size=2048000

update subquota
http://192.168.10.21:8080/admin/update_subquota?id=1&idSub=1&size=2048000

remove subquota
http://192.168.10.21:8080/admin/remove_subquota?id=1&idSub=1

get list of quotas
http://192.168.10.21:8080/admin/quota_list

get quota info
http://192.168.10.21:8080/admin/quota_info?id=1
*/

class moment {
    private $http;
    private $options = array(
        'protocol' => 'http',
        'server_ip' => '127.0.0.1',
        'server_port' => '8082',
        );

    public function __construct($http, $options) {
        $this->http = $http;
        $this->options = array_merge($this->options, $options);
        $this->http->setPort($this->options['server_port']);
    }

    public function add($id, $name, $url, $qid) {
        $result = $this->http->get("{$this->options['protocol']}://{$this->options['server_ip']}/admin/add_channel?conf_file=$id&title=".base64_encode($name)."&uri=".base64_encode($url)."&quota=".$qid);
        return trim($result) == 'OK' ? true : $result;
    }

    public function modify($id, $name, $url, $qid) {
        $result = $this->http->get("{$this->options['protocol']}://{$this->options['server_ip']}/admin/add_channel?conf_file=$id&title=".base64_encode($name)."&update&uri=".base64_encode($url)."&quota=".$qid);
        return trim($result) == 'OK' ? true : $result;
    }

    public function delete($id) {
        Notify::note("{$this->options['protocol']}://{$this->options['server_ip']}/admin/remove_channel?conf_file=$id");
        $result = $this->http->get("{$this->options['protocol']}://{$this->options['server_ip']}/admin/remove_channel?conf_file=$id");         
        return trim($result) == 'OK' ? true : $result;
    }

    public function playlist() {
        $this->http->setPort($this->options['web_port']);
        $result = $this->http->get("{$this->options['protocol']}://{$this->options['server_ip']}/server/playlist.json");
        $this->http->setPort($this->options['server_port']);
        return trim($result);
    }

    public function unixtime() {
        $this->http->setPort($this->options['web_port']);
        $result = $this->http->get("{$this->options['protocol']}://{$this->options['server_ip']}/mod_nvr/unixtime");
        $this->http->setPort($this->options['server_port']);
        return trim($result);
    }

    public function existence($stream) {

		// this shit work 10 times faster!!!
		$url = $this->options['protocol']."://".$this->options['server_ip'].":".$this->options['web_port']."/mod_nvr/existence?stream=".$stream;
		$https_user = "";
		$https_password = "";
		$opts = array('http' =>
		array(
			'method'  => 'GET',
			'header'  => "Content-Type: text/xml\r\n".
				"Authorization: Basic ".base64_encode("$https_user:$https_password")."\r\n",
				'content' => "",
				'timeout' => 60
			)
		);
		$context  = stream_context_create($opts);
		$result = @file_get_contents($url, false, $context, -1, 40000);
		return trim($result);

        $this->http->setPort($this->options['web_port']);
        $result = $this->http->get("{$this->options['protocol']}://{$this->options['server_ip']}/mod_nvr/existence?stream={$stream}");
        $this->http->setPort($this->options['server_port']);
        return trim($result);
    }

    public function resolution($stream) {
        $this->http->setPort($this->options['web_port']);
        $result = $this->http->get("{$this->options['protocol']}://{$this->options['server_ip']}/mod_nvr_admin/resolution?stream={$stream}");
        $this->http->setPort($this->options['server_port']);
        return trim($result);
    }

    public function alive($stream) {
        //return 1;
        $this->http->setPort($this->options['web_port']);
        $result = $this->http->get("{$this->options['protocol']}://{$this->options['server_ip']}/mod_nvr_admin/alive?stream={$stream}");
        $this->http->setPort($this->options['server_port']);
        return trim($result);
    }

    public function addQuota($id, $size) {
        $result = $this->http->get("{$this->options['protocol']}://{$this->options['server_ip']}/admin/add_quota?id={$id}&size={$size}");
        return trim($result);
    }    

    public function removeQuota($id) {
        $result = $this->http->get("{$this->options['protocol']}://{$this->options['server_ip']}/admin/remove_quota?id={$id}");
        return trim($result);
    }

    public function quotaList() {
        $result = $this->http->get("{$this->options['protocol']}://{$this->options['server_ip']}/admin/quota_list");
        return trim($result);
    }

    public function quotaInfo($id) {
        $result = $this->http->get("{$this->options['protocol']}://{$this->options['server_ip']}/admin/quota_info?id={$id}");
        return trim($result);
    }

    public function stat($type) {
        $stats = array(
            'load' => 'mod_nvr_admin/statistics',
            'disk' => 'mod_nvr_admin/disk_info',
            'source_info' => 'mod_nvr_admin/source_info',
            'rtmp' => 'mod_rtmp/stat',
            );
        $result = $this->http->get("{$this->options['protocol']}://{$this->options['server_ip']}/{$stats[$type]}");
        return trim($result);
    }

    public function rec($mode = 'on', $id) {
        $mode = $mode == 'on' ? 'on' : 'off';
        $result = $this->http->get("{$this->options['protocol']}://{$this->options['server_ip']}/mod_nvr_admin/rec_{$mode}?stream={$id}&seq=1");
        $result = json_decode($result, 1);
        return (bool)trim($result['recording']) == (bool)($mode == 'on' ? true : false);
    }

}

?>