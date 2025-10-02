# PDF Text Extraction Library Installation

This plugin supports PDF text extraction for importing PDF files. To enable this feature, you need to install the PDFParser library.

## Installation Options

### Option 1: Using Composer (Recommended)

1. Navigate to the plugin directory:
   ```bash
   cd wp-content/plugins/chatbot-nuwab
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

### Option 2: Manual Installation

1. Download the PDFParser library:
   ```bash
   composer require smalot/pdfparser
   ```

2. Or download from GitHub: https://github.com/smalot/pdfparser

### Option 3: System pdftotext Utility

If you have access to the server command line, you can install the `pdftotext` utility:

#### Ubuntu/Debian:
```bash
sudo apt-get install poppler-utils
```

#### CentOS/RHEL:
```bash
sudo yum install poppler-utils
```

#### macOS:
```bash
brew install poppler
```

#### Windows:
Download and install from: https://blog.alivate.com.au/poppler-windows/

## Verification

After installation, the PDF import feature will automatically detect and use the available PDF text extraction method:

1. **PDFParser Library** (Primary) - Most reliable for PHP applications
2. **pdftotext Utility** (Fallback) - System-level PDF text extraction
3. **Error Handling** - If neither is available, imports will show appropriate error messages

## Features

- **Text Extraction**: Extracts text content from PDF files
- **Formatting Options**: Choose to preserve or clean up text formatting
- **Image Extraction**: Option to extract images (future enhancement)
- **Error Handling**: Graceful fallback when extraction fails
- **Automatic Indexing**: Imported content is automatically indexed for search

## Troubleshooting

### Common Issues:

1. **"PDF text extraction not available"**
   - Install PDFParser library using Composer
   - Or install pdftotext utility on your system

2. **"PDF parsing failed"**
   - The PDF file might be corrupted or password-protected
   - Try with a different PDF file

3. **Memory Issues**
   - Large PDF files might cause memory issues
   - Consider increasing PHP memory limit

### Support

For issues with PDF text extraction, please check:
- PHP memory limit (recommended: 256MB or higher)
- File upload limits
- PDF file format compatibility





