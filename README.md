# Welcome
歡迎使用 Tellustek 建製的 Wordpress with Docker 開發佈建包
<br><br>

## 事前準備
- [docker](https://www.docker.com/products/docker-desktop/)
- [nodejs](https://nodejs.org/zh-tw/download) - 請使用 16 或是 18 等 LTS 版本
- (optional) deployment 會使用 [AWS CDK](https://docs.aws.amazon.com/cdk/v2/guide/getting_started.html#getting_started_install)
<br><br>
## 如何進行本地開發
可以使用 .env 對開發中環境進行一些設定, env.example 檔案有設定內容範例可以參考.
`npm start`
第一次執行會需要下載各種相依的容器, 會比較花時間
<br><br>

## 如何備份
`npm run backup` 執行後會跟據系統日期在 `backups` 資料夾中建立以日期為命名規則的靜態文件打包檔與 sql 備份檔.
<br><br>

## 如何還原
`npm run restore yyyyMMDD` 可以還原指定日期的打包檔
<br><br>

## 如何使用 akeeba 備份檔進行還原
`npm run akeeba-restore` 可以還原指定日期的打包檔
<br><br>

## 常見問題
* 重複的專案名稱 - 因為會用資料夾名成來建立 docker image 和 valume, 請盡量用不同的專案名稱或是刪除時手動將docker image與 volume一併刪除
<br><br>

## 關於佈建
...
<br><br>

## Useful commands
- `npm start` 啟動服務
- `npm stop` 關閉服務
- `npm run logs` 查看 web server logs
- `npm run backup` 備份
- `npm run restore yyyyMMDD` 還原
- `npm run akeeba-restore` 使用 akeeba 備份檔還原