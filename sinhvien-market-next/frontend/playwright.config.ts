import { defineConfig, devices } from "@playwright/test";

/**
 * E2E chạy trên bản build production (`next build` + `next start`) với dữ liệu mock,
 * để số đo tốc độ phản ánh đúng trải nghiệm thật (không phải chế độ dev).
 */
export default defineConfig({
  testDir: "tests/e2e",
  timeout: 30_000,
  fullyParallel: false,
  workers: 1, // chạy tuần tự để số đo tốc độ không bị nhiễu bởi test song song
  reporter: [["list"]],
  use: { baseURL: "http://localhost:3100", ...devices["Desktop Chrome"] },
  webServer: {
    command: "node node_modules/next/dist/bin/next start -p 3100",
    url: "http://localhost:3100/login",
    timeout: 120_000,
    reuseExistingServer: true,
  },
});
