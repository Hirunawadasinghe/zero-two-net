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

const __dirname = dirname(fileURLToPath(import.meta.url));
const allFiles = getFiles(join(__dirname, "../public/subtitle"));

writeFileSync(join(__dirname, "../public/database/file_list.json"), JSON.stringify(allFiles));

console.log("File list generated.");
