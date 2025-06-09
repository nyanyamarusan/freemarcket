# フリマアプリ

## 環境構築

Dockerビルド
 1. git clone git@github.com:nyanyamarusan/freemarcket.git
 2. docker-compose up -d --build

＊ MySQLは、OSによって起動しない場合があるのでそれぞれのPCに合わせて docker-compose.yml ファイルを編集してください。

Laravel環境構築

 1. docker-compose exec php bash
 2. composer install
 3. .env.exampleファイルから.envを作成し、環境変数を変更
 4. php artisan key:generate
 5. php artisan migrate
 6. php artisan db:seed

## 使用技術

- PHP 8.3-fpm
- Laravel 12.14.1
- MySQL 8.0.41
- nginx 1.26.3
- Fortify
- mailhog
- Stripe
- Laravel Dusk（ブラウザテスト）

## ER図

![ER図](/freemarcket.drawio.png)

## URL

- 開発環境：http://localhost/
- ユーザー登録：http://localhost/register
- phpMyAdmin：http://localhost:8080/
# freemarcket
