<?php

$qiita = new Qiita(array(
	'username' => 'suin',
	'password' => 'p@ssW0rd',
));

try
{
	// ユーザ情報の取得
	$user = $qiita->api('/users/suin');

	// 投稿の実行
	$createdItem = $qiita->api('/items', 'POST', array(
		'title' => 'Qiita APIからのテスト投稿',
		'tags' => array(
			array('name' => 'Qiita'),
		),
		'body' => 'テスト投稿',
		'private' => false,
	));
}
catch ( Exception $e )
{
	error_log($e);
}