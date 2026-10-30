import { spawn } from "node:child_process";
import { resolve } from "node:path";

const nextBin = resolve("node_modules/next/dist/bin/next");
const extraArgs = process.argv.slice(2);

const child = spawn(process.execPath, [nextBin, "dev", ...extraArgs], {
  stdio: ["inherit", "pipe", "pipe"],
  env: { ...process.env, FORCE_COLOR: "1" },
});

let localUrl = "http://localhost:3000";
let opened = false;

function openBrowser(url) {
  if (opened || process.env.CI || process.env.NO_OPEN === "1") return;
  opened = true;

  if (process.platform === "win32") {
    spawn("cmd", ["/c", "start", "", url], { detached: true, stdio: "ignore" }).unref();
  } else if (process.platform === "darwin") {
    spawn("open", [url], { detached: true, stdio: "ignore" }).unref();
  } else {
    spawn("xdg-open", [url], { detached: true, stdio: "ignore" }).unref();
  }
}

child.stdout.on("data", (chunk) => {
  process.stdout.write(chunk);
  const text = chunk.toString().replace(/\x1b\[[0-9;]*m/g, "");

  const match = text.match(/Local:\s+(https?:\/\/localhost:\d+)/i);
  if (match) {
    localUrl = match[1];
  }

  if (!opened && /Ready in/i.test(text)) {
    openBrowser(localUrl);
  }
});

child.stderr.on("data", (chunk) => {
  process.stderr.write(chunk);
});

child.on("exit", (code) => {
  process.exit(code ?? 0);
});
