# Welcome to your CDK TypeScript project

This is a blank project for CDK development with TypeScript.

The `cdk.json` file tells the CDK Toolkit how to execute your app.

## Useful commands

* `npm run build`   compile typescript to js
* `npm run watch`   watch for changes and compile
* `npm run test`    perform the jest unit tests
* `cdk deploy`      deploy this stack to your default AWS account/region
* `cdk diff`        compare deployed stack with current state
* `cdk synth`       emits the synthesized CloudFormation template

## 事前準備
- 請先安裝好 docker 與 nodejs, 如果要執行佈建會需要先安裝 aws-cli
- `cp env.example .env` 並且修改 .env 中的內容

## 如何進行本地開發
.env檔案可以設定本地開發時打算使用的hostname與port, 執行以下命令開始執行 Server
```
docker compose up
```
第一次執行會需要下載各種相依的容器, 會比較花時間

## 如何還原
如果尚未進行過 docker compose up, 可使用以下操作:<br>
將akeeba備份的zip或是jpa備份檔, 放入 src/restore 目錄中, 執行 docker compose up時就會自動複製並且在網站根目錄產生 restore.php 檔按提供還原使用.

已經執行過 docker compose up 有以下兩種選擇
* Option 1. 清除所有既有資料重新 build 新的 image<br>
  `npm run rebuildimage`<br>
  完成後再次執行<br>
  `docker compose up`
* Option 2. Copy備份資料進入容器內<br>
  `npm run restore`

## 常見問題
* 如何執行容器<br>`docker compose up`
* 如何停止容器執行<br>`ctrl+c`
* 如何將本地開發中資料刪除乾淨<br>`npm run cleanup`
