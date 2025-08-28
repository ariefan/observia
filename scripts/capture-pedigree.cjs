const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

async function capturePedigree(livestockId, outputPath) {
  let browser = null;
  
  try {
    // Launch browser
    browser = await puppeteer.launch({
      headless: true,
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-accelerated-2d-canvas',
        '--no-first-run',
        '--no-zygote',
        '--disable-gpu'
      ]
    });

    const page = await browser.newPage();
    
    // Set viewport for consistent rendering - wider to match pedigree layout
    await page.setViewport({
      width: 1800,
      height: 1000,
      deviceScaleFactor: 2 // High DPI for better quality
    });

    // Navigate to the pedigree image route using provided base URL
    const url = `${baseUrl}/livestocks/${livestockId}/pedigree-image`;
    await page.goto(url, { 
      waitUntil: 'networkidle0',
      timeout: 30000 
    });

    // Wait for the pedigree component to be fully loaded
    await page.waitForSelector('.pedigree-container', { timeout: 10000 });
    
    // Wait a bit more for any animations or dynamic content
    await new Promise(resolve => setTimeout(resolve, 2000));

    // Find the pedigree container element
    const pedigreeElement = await page.$('.pedigree-container');
    
    if (!pedigreeElement) {
      throw new Error('Pedigree container not found');
    }

    // Take screenshot of just the pedigree element
    const screenshot = await pedigreeElement.screenshot({
      path: outputPath,
      type: 'jpeg',
      quality: 95
    });

    console.log(`Pedigree image captured successfully: ${outputPath}`);
    return true;

  } catch (error) {
    console.error('Error capturing pedigree:', error.message);
    return false;
  } finally {
    if (browser) {
      await browser.close();
    }
  }
}

// Get command line arguments
const args = process.argv.slice(2);
if (args.length < 3) {
  console.error('Usage: node capture-pedigree.cjs <livestock_id> <output_path> <base_url>');
  process.exit(1);
}

const livestockId = args[0];
const outputPath = args[1];
const baseUrl = args[2];

// Ensure output directory exists
const outputDir = path.dirname(outputPath);
if (!fs.existsSync(outputDir)) {
  fs.mkdirSync(outputDir, { recursive: true });
}

// Execute capture
capturePedigree(livestockId, outputPath)
  .then(success => {
    process.exit(success ? 0 : 1);
  })
  .catch(error => {
    console.error('Unhandled error:', error);
    process.exit(1);
  });