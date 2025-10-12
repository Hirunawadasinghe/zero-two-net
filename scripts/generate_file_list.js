import { readdirSync, writeFileSync } from "fs";
import { join } from "path";

function getFiles(dir, files = []) {
  const items = readdirSync(dir, { withFileTypes: true });
  for (const item of items) {
    if (item.isDirectory()) {
      getFiles(join(dir, item.name), files);
    } else {
      files.push(join(dir, item.name));
    }
  }
  return files;
}

const allFiles = getFiles("./dist/subtitle");
writeFileSync("./dist/database/file_list.json", JSON.stringify(allFiles));

const TELEGRAM_TOKEN = "8408589149:AAHtCvNCvJqCDMCOd7A_7opOz6Od6L8dpGM";
const TELEGRAM_API = `https://api.telegram.org/bot${TELEGRAM_TOKEN}`;
const admin_chat = '5922865116';
await fetch(`${TELEGRAM_API}/sendMessage`, {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({
    chat_id: admin_chat,
    text: `done`
  })
});

console.log("File list generated.");
