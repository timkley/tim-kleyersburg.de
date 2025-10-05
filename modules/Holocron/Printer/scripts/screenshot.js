#!/usr/bin/env node

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

// Configuration
const PRINTER_WIDTH = 512;
const DEFAULT_HEIGHT = 400;

// Performance optimized browser launch args
const BROWSER_ARGS = [
    '--disable-web-security',
    '--disable-features=TranslateUI',
    '--disable-ipc-flooding-protection',
    '--disable-renderer-backgrounding',
    '--disable-backgrounding-occluded-windows',
    '--disable-background-networking',
    '--disable-default-apps',
    '--disable-extensions',
    '--disable-sync',
    '--disable-background-timer-throttling',
    '--disable-dev-shm-usage',
    '--no-sandbox',
    '--no-first-run',
    '--no-default-browser-check',
];

async function takeScreenshot(htmlContent, outputPath) {
    const startTime = Date.now();
    
    let browser;
    try {
        // Launch browser with optimized settings
        browser = await chromium.launch({
            headless: true,
            args: BROWSER_ARGS
        });

        // Create page with printer dimensions
        const page = await browser.newPage({
            viewport: { 
                width: PRINTER_WIDTH, 
                height: DEFAULT_HEIGHT 
            },
            deviceScaleFactor: 1
        });

        // Set content and wait for DOM (CSS is inline, no external resources)
        await page.setContent(htmlContent, { 
            waitUntil: 'domcontentloaded',
            timeout: 5000
        });

        // Use body element for screenshot
        const targetElement = page.locator('body');

        // Take screenshot with optimized settings
        const screenshotBuffer = await targetElement.screenshot({
            type: 'png',
            animations: 'disabled'
        });

        // Write to output file
        await fs.promises.writeFile(outputPath, screenshotBuffer);

        const duration = Date.now() - startTime;
        
        // Return success result
        return {
            success: true,
            outputPath,
            duration,
            size: screenshotBuffer.length
        };

    } catch (error) {
        return {
            success: false,
            error: error.message,
            duration: Date.now() - startTime
        };
    } finally {
        if (browser) {
            await browser.close();
        }
    }
}

async function main() {
    const args = process.argv.slice(2);
    
    if (args.length < 2) {
        console.error('Usage: node screenshot.js <html-file-or-stdin> <output-path>');
        process.exit(1);
    }

    const [htmlSource, outputPath] = args;
    let htmlContent;

    try {
        // Read HTML content from file or stdin
        if (htmlSource === '-' || htmlSource === 'stdin') {
            // Read from stdin
            const chunks = [];
            for await (const chunk of process.stdin) {
                chunks.push(chunk);
            }
            htmlContent = Buffer.concat(chunks).toString('utf8');
        } else {
            // Read from file
            htmlContent = await fs.promises.readFile(htmlSource, 'utf8');
        }

        if (!htmlContent.trim()) {
            throw new Error('No HTML content provided');
        }

        // Ensure output directory exists
        const outputDir = path.dirname(outputPath);
        await fs.promises.mkdir(outputDir, { recursive: true });

        // Take screenshot
        const result = await takeScreenshot(htmlContent, outputPath);

        if (result.success) {
            console.log(JSON.stringify({
                success: true,
                outputPath: result.outputPath,
                duration: result.duration,
                size: result.size
            }));
            process.exit(0);
        } else {
            console.error(JSON.stringify({
                success: false,
                error: result.error,
                duration: result.duration
            }));
            process.exit(1);
        }

    } catch (error) {
        console.error(JSON.stringify({
            success: false,
            error: error.message,
            duration: 0
        }));
        process.exit(1);
    }
}

// Handle uncaught errors gracefully
process.on('uncaughtException', (error) => {
    console.error(JSON.stringify({
        success: false,
        error: `Uncaught exception: ${error.message}`,
        duration: 0
    }));
    process.exit(1);
});

process.on('unhandledRejection', (reason) => {
    console.error(JSON.stringify({
        success: false,
        error: `Unhandled rejection: ${reason}`,
        duration: 0
    }));
    process.exit(1);
});

if (require.main === module) {
    main();
}

module.exports = { takeScreenshot };
