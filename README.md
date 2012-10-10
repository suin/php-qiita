# PHP Qiita (v.1.0.0)

QiitaAPIを扱うためのPHP向けライブラリ

The build status of the current master branch is tracked by Travis CI: [![Build Status](https://secure.travis-ci.org/suin/php-qiita.png?branch=master)](http://travis-ci.org/suin/php-qiita)


## 要件

* PHP 5.3 以上
* curl拡張

## インストール

[Composer](https://github.com/composer/composer)経由でインストールします。

まず `composer.json` ファイルに下記を記述します:

```json
{
	"require": {
		"suin/php-qiita": ">=1.0.0"
	}
}
```

composerを走らせてインストールします:

```
$ composer install
```

最後に、あなたのプロダクトで `vendor/autoload.php` をインクルードします:

```
require_once 'vendor/autoload.php';
```


## 使い方

```php
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
```

## License

php-qiita is licensed under the MIT License - see the LICENSE file for details