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

const allFiles = getFiles("./public/subtitle");
writeFileSync("./public/database/file_list.json", JSON.stringify(allFiles));

console.log("File list generated.");
