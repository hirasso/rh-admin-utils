import { test, expect } from "@playwright/test";

test.describe("WP Admin", () => {
  test("Redirects the admin dashboard to the Pages edit screen", async ({ page }) => {
    await page.goto("/wp-admin/index.php");
    await expect(page.locator('h1')).toContainText('Pages');
    await expect(page).toHaveURL(/edit\.php\?post_type=page/);
  });

  test("Loads the admin assets without JS errors", async ({ page }) => {
    const errors: Error[] = [];
    page.on("pageerror", (error) => errors.push(error));
    await page.goto("/wp-admin/edit.php?post_type=page");
    await expect(page.locator('link[href*="rhau-admin.css"]')).toHaveCount(1);
    await expect(page.locator('script[src*="rhau-admin.js"]')).toHaveCount(1);
    expect(errors).toEqual([]);
  });
});
