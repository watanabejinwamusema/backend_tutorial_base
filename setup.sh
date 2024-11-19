# .envファイルを作成
cp .env.example .env
if [ ! -f .env ]; then
    echo ".envファイルの作成に失敗しました"
    exit 1
fi

# パッケージのインストール
docker run --rm \
    -v $(pwd):/opt \
    -w /opt \
    laravelsail/php83-composer:latest \
    bash -c "composer install"
if [ $? -ne 0 ]; then
    echo "パッケージのインストールに失敗しました"
    exit 1
fi

# sailエイリアスを作成（Uzoneの構成に合わせている）
ln -s ./vendor/bin/sail ./sail
if [ ! -f ./sail ]; then
    echo "sailエイリアスの作成に失敗しました"
    exit 1
fi

# コンテナのビルド・起動
./sail up -d --build
if [ $? -ne 0 ]; then
    echo "コンテナのビルド・起動に失敗しました"
    exit 1
fi

# APP_KEYの生成
./sail artisan key:generate
if [ $? -ne 0 ]; then
    echo "APP_KEYの生成に失敗しました"
    exit 1
fi
