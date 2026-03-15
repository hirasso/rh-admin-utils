import { test, expect } from "@playwright/test";

test.describe("WP Admin", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto("/wp-admin/");
  });

  test("Can access the admin dashboard", async ({ page }) => {
    expect(page.locator('h1')).toContainText('Dashboard');
  });
});
