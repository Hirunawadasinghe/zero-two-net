import { readdirSync } from "fs";

export async function onRequest(context) {
  const files = readdirSync("./subtitle");
  return new Response(JSON.stringify(files), {
    headers: { "Content-Type": "application/json" },
  });
}
