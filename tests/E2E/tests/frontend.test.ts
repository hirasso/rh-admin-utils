import { test, expect } from "@playwright/test";

test.describe("Frontend", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto("/");
  });

  test("renders the frontend form", async ({ page }) => {
    await page.goto("/test-page");
    expect(page.locator('acf-frontend-form')).not.toBeNull();
  });

  test("initializes the frontend form", async ({ page }) => {
    await page.goto("/test-page");
    expect(page.locator('acf-frontend-form')).toHaveAttribute('initialized');
  });
});
