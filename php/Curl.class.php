<?php

class Curl {

	/*
	 * get 方式获取访问指定地址
	 * @param  string url 要访问的地址
	 * @param  string cookie cookie的存放地址,没有则不发送cookie
	 * @return string curl_exec()获取的信息
	 * @author andy
	 **/
	public function get( $url, $cookie='' )
	{
		// 初始化一个cURL会话
		$curl = curl_init($url);
		// 不显示header信息
		curl_setopt($curl, CURLOPT_HEADER, 0);
		// 将 curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		// 使用自动跳转
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		if(!empty($cookie)) {
			// 包含cookie数据的文件名，cookie文件的格式可以是Netscape格式，或者只是纯HTTP头部信息存入文件。
			curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie);
		}
		// 自动设置Referer
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
		// 执行一个curl会话
		$tmp = curl_exec($curl);
		// 关闭curl会话
		curl_close($curl);
		return $tmp;
	}

	/*
	 * post 方式模拟请求指定地址
	 * @param  string url	请求的指定地址
	 * @param  array  params 请求所带的
	 * #patam  string cookie cookie存放地址
	 * @return string curl_exec()获取的信息
	 * @author andy
	 **/
	public function post( $url, $params, $cookie )
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		// 对认证证书来源的检查，0表示阻止对证书的合法性的检查。
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		// 从证书中检查SSL加密算法是否存在
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
		//模拟用户使用的浏览器，在HTTP请求中包含一个”user-agent”头的字符串。
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		//发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
		curl_setopt($curl, CURLOPT_POST, 1);
		// 将 curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		// 使用自动跳转
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); 
		// 自动设置Referer
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
		// Cookie地址
		curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie);
		// 全部数据使用HTTP协议中的"POST"操作来发送。要发送文件，
		// 在文件名前面加上@前缀并使用完整路径。这个参数可以通过urlencoded后的字符串
		// 类似'para1=val1&para2=val2&...'或使用一个以字段名为键值，字段数据为值的数组
		// 如果value是一个数组，Content-Type头将会被设置成multipart/form-data。
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}
	/**
	 * curl 下载远程文件保存到本地
	 * @param string remote 远程地址
	 * @param string local 本地保存地址
	 * @param string cookie cookie位置由于某些网站有访
	 * 问限制例如路透社你会发现如果你没有cookie是无法获
	 * 取该网站上的内容，所以要先模拟登陆获取cookie，
	 * 再带着cookie去请求远程地址
	 * @return void
	 * @author andy
	 */
	public function curlDownload( $remote, $local, $cookie = '' ) {
		$cp = curl_init($remote);
		$fp = fopen($local,"w");
		curl_setopt($cp, CURLOPT_FILE, $fp);
		curl_setopt($cp, CURLOPT_HEADER, 0);
		if($cookie != ''){
			curl_setopt($cp, CURLOPT_COOKIEFILE, $cookie);
		}
		curl_exec($cp);
		curl_close($cp);
		fclose($fp);
	}


}
