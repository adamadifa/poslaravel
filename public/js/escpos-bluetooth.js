/**
 * ESC/POS Bluetooth Thermal Printer Driver
 * Supports 58mm and 80mm thermal printers via Web Bluetooth API.
 * Includes standard GATT services and characteristics for wireless mini printers.
 */
class PosBluetoothPrinter {
    constructor() {
        this.device = null;
        this.server = null;
        this.characteristic = null;
        this.paperWidth = localStorage.getItem('pos_printer_width') || '58'; // '58' (32 chars) or '80' (42/48 chars)
        this.isConnected = false;
        this.deviceName = '';
        this.statusListeners = [];

        // Known thermal printer Bluetooth GATT Services & Characteristics UUIDs
        this.SERVICES = [
            '000018f0-0000-1000-8000-00805f9b34fb', // Standard Serial / POS Printer Service
            'e7810a71-73ae-499d-8c15-faa9aef0c3f2', // Nordic UART Service commonly used by mobile printers
            '49535343-fe7d-4ae5-8fa9-9fafd205e455', // ISSC Transparent Service
            '0000ff00-0000-1000-8000-00805f9b34fb', // Common generic serial
            '0000fee7-0000-1000-8000-00805f9b34fb', // Tengda / Xprinter
        ];

        this.autoPrint = localStorage.getItem('pos_printer_autoprint') === 'true';
    }

    isSupported() {
        return !!(navigator.bluetooth && navigator.bluetooth.requestDevice);
    }

    getUnsupportedReason() {
        if (!window.isSecureContext) {
            return 'Web Bluetooth memerlukan koneksi aman (HTTPS atau localhost/127.0.0.1). Jika diakses via IP LAN (misal: 192.168.x.x atau domain http), Chrome akan memblokir fitur Bluetooth.';
        }
        if (!navigator.bluetooth) {
            return 'Fitur Web Bluetooth dinonaktifkan di Chrome Anda. Anda bisa mengaktifkannya di chrome://flags/#enable-web-bluetooth lalu restart Chrome.';
        }
        return 'Browser ini belum mendukung Web Bluetooth API.';
    }

    onStatusChange(callback) {
        this.statusListeners.push(callback);
    }

    notifyStatus(status, message = '') {
        this.statusListeners.forEach(cb => cb({
            connected: this.isConnected,
            name: this.deviceName,
            status,
            message
        }));
    }

    setPaperWidth(width) {
        this.paperWidth = width === '80' ? '80' : '58';
        localStorage.setItem('pos_printer_width', this.paperWidth);
    }

    setAutoPrint(val) {
        this.autoPrint = !!val;
        localStorage.setItem('pos_printer_autoprint', this.autoPrint ? 'true' : 'false');
    }

    getMaxChars() {
        return this.paperWidth === '80' ? 44 : 32;
    }

    /**
     * Reconnect to an already-paired device (remembered device in Chrome)
     */
    async autoReconnect() {
        if (!this.isSupported() || !navigator.bluetooth.getDevices) {
            return false;
        }

        try {
            const devices = await navigator.bluetooth.getDevices();
            if (!devices || devices.length === 0) return false;

            const lastDevName = localStorage.getItem('pos_last_bt_printer');
            let matched = null;
            if (lastDevName) {
                matched = devices.find(d => d.name === lastDevName);
            }
            if (!matched && devices.length > 0) {
                matched = devices[0];
            }

            if (matched) {
                return await this.attachDevice(matched);
            }
        } catch (e) {
            console.warn('Auto reconnect bluetooth failed:', e);
        }
        return false;
    }

    async attachDevice(device) {
        this.device = device;
        this.deviceName = device.name || 'Bluetooth Printer';

        device.addEventListener('gattserverdisconnected', () => {
            this.isConnected = false;
            this.characteristic = null;
            this.notifyStatus('disconnected', 'Koneksi printer terputus.');
        });

        // If not connected yet
        let server = device.gatt;
        if (!server.connected) {
            server = await device.gatt.connect();
        }
        this.server = server;

        let targetChar = null;
        for (const serviceUuid of this.SERVICES) {
            try {
                const service = await server.getPrimaryService(serviceUuid);
                if (service) {
                    const chars = await service.getCharacteristics();
                    for (const c of chars) {
                        if (c.properties.write || c.properties.writeWithoutResponse) {
                            targetChar = c;
                            break;
                        }
                    }
                }
            } catch (e) {
                // Ignore service not found
            }
            if (targetChar) break;
        }

        if (!targetChar) {
            try {
                const services = await server.getPrimaryServices();
                for (const service of services) {
                    const chars = await service.getCharacteristics();
                    for (const c of chars) {
                        if (c.properties.write || c.properties.writeWithoutResponse) {
                            targetChar = c;
                            break;
                        }
                    }
                    if (targetChar) break;
                }
            } catch (e) {}
        }

        if (!targetChar) {
            throw new Error('Tidak dapat menemukan jalur komunikasi (GATT Writable) pada printer ini.');
        }

        this.characteristic = targetChar;
        this.isConnected = true;

        localStorage.setItem('pos_last_bt_printer', this.deviceName);
        this.notifyStatus('connected', `Terhubung ke ${this.deviceName}`);
        return this.deviceName;
    }

    async connect() {
        if (!this.isSupported()) {
            throw new Error(this.getUnsupportedReason());
        }

        try {
            this.notifyStatus('connecting', 'Mencari printer Bluetooth...');
            
            // Request Bluetooth device
            const device = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: this.SERVICES
            });

            if (!device) {
                throw new Error('Perangkat printer tidak dipilih.');
            }

            return await this.attachDevice(device);
        } catch (err) {
            this.isConnected = false;
            this.notifyStatus('error', err.message);
            throw err;
        }
    }

    async disconnect() {
        if (this.device && this.device.gatt.connected) {
            this.device.gatt.disconnect();
        }
        this.isConnected = false;
        this.characteristic = null;
        this.notifyStatus('disconnected', 'Printer Bluetooth diputus.');
    }

    /**
     * Send raw buffer in chunks to avoid BLE characteristic buffer overflow
     */
    async sendBuffer(buffer) {
        if (!this.isConnected || !this.characteristic) {
            throw new Error('Printer Bluetooth belum terhubung.');
        }

        const CHUNK_SIZE = 64; // Safe BLE MTU write size
        for (let i = 0; i < buffer.length; i += CHUNK_SIZE) {
            const chunk = buffer.slice(i, i + CHUNK_SIZE);
            if (this.characteristic.writeValueWithoutResponse) {
                await this.characteristic.writeValueWithoutResponse(chunk);
            } else {
                await this.characteristic.writeValue(chunk);
            }
            await new Promise(r => setTimeout(r, 25));
        }
    }

    encodeText(text) {
        const encoder = new TextEncoder();
        return encoder.encode(text);
    }

    concatBuffers(...buffers) {
        let totalLength = 0;
        for (const b of buffers) totalLength += b.length;
        const result = new Uint8Array(totalLength);
        let offset = 0;
        for (const b of buffers) {
            result.set(b, offset);
            offset += b.length;
        }
        return result;
    }

    // Standard ESC/POS commands
    CMD = {
        INIT: new Uint8Array([0x1B, 0x40]),
        ALIGN_LEFT: new Uint8Array([0x1B, 0x61, 0x00]),
        ALIGN_CENTER: new Uint8Array([0x1B, 0x61, 0x01]),
        ALIGN_RIGHT: new Uint8Array([0x1B, 0x61, 0x02]),
        BOLD_ON: new Uint8Array([0x1B, 0x45, 0x01]),
        BOLD_OFF: new Uint8Array([0x1B, 0x45, 0x00]),
        PAPER_FEED_3: new Uint8Array([0x1B, 0x64, 0x03]),
        PAPER_FEED_5: new Uint8Array([0x1B, 0x64, 0x05]),
    };

    /**
     * Convert Image URL to ESC/POS Raster Bit Image (GS v 0) Buffer
     * Works across thermal printers supporting standard ESC/POS raster bitmaps
     */
    async loadImageAsEscPosRaster(imageUrl, targetMaxWidth = null) {
        if (!imageUrl) return null;
        try {
            const img = new Image();
            img.crossOrigin = 'Anonymous';
            await new Promise((resolve, reject) => {
                img.onload = () => resolve();
                img.onerror = (e) => reject(new Error('Gagal memuat file logo'));
                img.src = imageUrl;
            });

            const maxWidth = targetMaxWidth || (this.paperWidth === '80' ? 384 : 240);
            let w = img.naturalWidth || img.width;
            let h = img.naturalHeight || img.height;

            if (w > maxWidth) {
                h = Math.round((h * maxWidth) / w);
                w = maxWidth;
            }

            // ESC/POS raster width must be multiple of 8
            const byteWidth = Math.ceil(w / 8);
            const rasterWidth = byteWidth * 8;
            const rasterHeight = h;

            const canvas = document.createElement('canvas');
            canvas.width = rasterWidth;
            canvas.height = rasterHeight;
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, rasterWidth, rasterHeight);
            
            // Draw centered
            const xOffset = Math.floor((rasterWidth - w) / 2);
            ctx.drawImage(img, xOffset, 0, w, h);

            const imgData = ctx.getImageData(0, 0, rasterWidth, rasterHeight);
            const pixels = imgData.data;

            // Header for ESC/POS GS v 0 (Print raster bit image)
            // GS v 0 m xL xH yL yH d1...dk
            const xL = byteWidth % 256;
            const xH = Math.floor(byteWidth / 256);
            const yL = rasterHeight % 256;
            const yH = Math.floor(rasterHeight / 256);

            const header = [0x1D, 0x76, 0x30, 0x00, xL, xH, yL, yH];
            const bitmap = new Uint8Array(header.length + (byteWidth * rasterHeight));
            bitmap.set(header, 0);

            let offset = header.length;
            for (let y = 0; y < rasterHeight; y++) {
                for (let xByte = 0; xByte < byteWidth; xByte++) {
                    let byteVal = 0;
                    for (let bit = 0; bit < 8; bit++) {
                        const x = (xByte * 8) + bit;
                        const pIdx = (y * rasterWidth + x) * 4;
                        const r = pixels[pIdx];
                        const g = pixels[pIdx + 1];
                        const b = pixels[pIdx + 2];
                        const a = pixels[pIdx + 3];

                        // Grayscale luminance calculation
                        const gray = (r * 0.299 + g * 0.587 + b * 0.114);
                        // 1 = black (burn dot), 0 = white
                        const isBlack = (a > 64 && gray < 165);
                        if (isBlack) {
                            byteVal |= (1 << (7 - bit));
                        }
                    }
                    bitmap[offset++] = byteVal;
                }
            }
            return bitmap;
        } catch (err) {
            console.warn('Gagal memproses bitmap logo untuk printer thermal:', err);
            return null;
        }
    }

    formatRow(left, right, maxLen = null) {
        const width = maxLen || this.getMaxChars();
        left = String(left || '');
        right = String(right || '');

        if (left.length + right.length > width) {
            const allowedLeft = width - right.length - 1;
            if (allowedLeft > 3) {
                left = left.substring(0, allowedLeft) + ' ';
            }
        }

        const spaces = Math.max(1, width - left.length - right.length);
        return left + ' '.repeat(spaces) + right + '\n';
    }

    formatDivider(char = '-', maxLen = null) {
        const width = maxLen || this.getMaxChars();
        return char.repeat(width) + '\n';
    }

    /**
     * Print Test Page
     */
    async printTestPage() {
        const max = this.getMaxChars();
        let payload = this.concatBuffers(
            this.CMD.INIT,
            this.CMD.ALIGN_CENTER,
            this.CMD.BOLD_ON,
            this.encodeText('TEST PRINTER BLUETOOTH\n'),
            this.CMD.BOLD_OFF,
            this.encodeText(`Kertas: ${this.paperWidth}mm (${max} Karakter)\n`),
            this.encodeText(this.formatDivider('=', max)),
            this.CMD.ALIGN_LEFT,
            this.encodeText(this.formatRow('Status:', 'TERHUBUNG', max)),
            this.encodeText(this.formatRow('Printer:', this.deviceName, max)),
            this.encodeText(this.formatRow('Waktu:', new Date().toLocaleTimeString('id-ID'), max)),
            this.encodeText(this.formatDivider('-', max)),
            this.CMD.ALIGN_CENTER,
            this.encodeText('Koneksi Bluetooth Berhasil!\nPrinter siap dipakai kasir.\n'),
            this.CMD.PAPER_FEED_5
        );

        await this.sendBuffer(payload);
    }

    /**
     * Print POS Retail / Dining Sale Receipt
     */
    async printSaleReceipt(sale, storeInfo = {}) {
        const max = this.getMaxChars();
        const storeName = (storeInfo.name || sale.warehouse?.name || 'KASIR POS').toUpperCase();
        const storeTagline = storeInfo.tagline || 'Sistem Kasir & Point of Sale';
        const storePhone = storeInfo.phone || sale.warehouse?.phone || '';
        const storeAddress = storeInfo.address || sale.warehouse?.address || '';

        const buffers = [
            this.CMD.INIT,
            this.CMD.ALIGN_CENTER,
        ];

        // Print Logo Bitmap if available and showLogo is enabled
        if (storeInfo.showLogo && storeInfo.logoUrl) {
            try {
                const logoBuffer = await this.loadImageAsEscPosRaster(storeInfo.logoUrl);
                if (logoBuffer) {
                    buffers.push(logoBuffer, this.CMD.PAPER_FEED_3);
                }
            } catch (err) {
                console.warn('Lewati cetak logo raster:', err);
            }
        }

        buffers.push(
            this.CMD.BOLD_ON,
            this.encodeText(storeName + '\n'),
            this.CMD.BOLD_OFF
        );

        if (storeTagline) buffers.push(this.encodeText(storeTagline + '\n'));
        if (storeAddress) buffers.push(this.encodeText(storeAddress + '\n'));
        if (storePhone) buffers.push(this.encodeText('Telp: ' + storePhone + '\n'));

        buffers.push(
            this.encodeText(this.formatDivider('=', max)),
            this.CMD.ALIGN_LEFT,
            this.encodeText(this.formatRow('No. Struk', sale.invoice_number || '-', max)),
            this.encodeText(this.formatRow('Waktu', new Date(sale.sale_date || Date.now()).toLocaleString('id-ID'), max)),
            this.encodeText(this.formatRow('Kasir', sale.user?.name || 'Kasir', max)),
            this.encodeText(this.formatRow('Pelanggan', sale.customer?.name || 'Umum', max))
        );

        if (sale.dining_table) {
            buffers.push(this.encodeText(this.formatRow('Meja', `Meja ${sale.dining_table.table_number}`, max)));
        }

        buffers.push(this.encodeText(this.formatDivider('-', max)));

        // Items
        (sale.items || []).forEach(it => {
            const name = it.product ? it.product.name : 'Item';
            const qty = it.quantity || 1;
            const price = parseInt(it.unit_price || 0);
            const subtotal = parseInt(it.subtotal || 0);

            buffers.push(this.encodeText(name + '\n'));
            buffers.push(this.encodeText(this.formatRow(`  ${qty} x ${price.toLocaleString('id-ID')}`, subtotal.toLocaleString('id-ID'), max)));

            if (it.modifiers && it.modifiers.length > 0) {
                const mods = it.modifiers.map(m => '+ ' + (m.modifier_name || m.name)).join(', ');
                buffers.push(this.encodeText(`  ${mods}\n`));
            }
        });

        buffers.push(this.encodeText(this.formatDivider('-', max)));

        // Totals
        const subtotalVal = parseInt(sale.subtotal || 0);
        const discVal = parseInt(sale.discount_amount || 0);
        const grandTotalVal = parseInt(sale.grand_total || 0);
        const paidVal = parseInt(sale.paid_amount || 0);
        const changeVal = parseInt(sale.change_amount || 0);

        buffers.push(this.encodeText(this.formatRow('Subtotal', `Rp ${subtotalVal.toLocaleString('id-ID')}`, max)));
        if (discVal > 0) {
            buffers.push(this.encodeText(this.formatRow('Diskon', `-Rp ${discVal.toLocaleString('id-ID')}`, max)));
        }

        buffers.push(
            this.CMD.BOLD_ON,
            this.encodeText(this.formatRow('TOTAL', `Rp ${grandTotalVal.toLocaleString('id-ID')}`, max)),
            this.CMD.BOLD_OFF,
            this.encodeText(this.formatRow(`Bayar (${(sale.payment_method || 'TUNAI').toUpperCase()})`, `Rp ${paidVal.toLocaleString('id-ID')}`, max)),
            this.encodeText(this.formatRow('Kembalian', `Rp ${changeVal.toLocaleString('id-ID')}`, max)),
            this.encodeText(this.formatDivider('=', max)),
            this.CMD.ALIGN_CENTER,
            this.encodeText('Terima kasih atas kunjungan Anda!\nBarang dibeli tidak dapat ditukar.\n'),
            this.CMD.PAPER_FEED_5
        );

        const finalBuffer = this.concatBuffers(...buffers);
        await this.sendBuffer(finalBuffer);
    }

    /**
     * Print Agent Banking & PPOB Receipt
     */
    async printAgentReceipt(tx, storeInfo = {}) {
        const max = this.getMaxChars();
        let title = 'STRUK TRANSAKSI AGEN';
        if (tx.service_type === 'BANK_TRANSFER') title = 'STRUK TRANSFER BANK';
        else if (tx.service_type === 'CASH_WITHDRAWAL') title = 'STRUK TARIK TUNAI';
        else title = 'STRUK PULSA & PPOB';

        const buffers = [
            this.CMD.INIT,
            this.CMD.ALIGN_CENTER,
        ];

        // Print Logo Bitmap if available and showLogo is enabled
        if (storeInfo.showLogo && storeInfo.logoUrl) {
            try {
                const logoBuffer = await this.loadImageAsEscPosRaster(storeInfo.logoUrl);
                if (logoBuffer) {
                    buffers.push(logoBuffer, this.CMD.PAPER_FEED_3);
                }
            } catch (err) {
                console.warn('Lewati cetak logo raster agen:', err);
            }
        }

        buffers.push(
            this.CMD.BOLD_ON,
            this.encodeText(title + '\n'),
            this.CMD.BOLD_OFF,
            this.encodeText('LAYANAN DIGITAL & PERBANKAN\n'),
            this.encodeText(this.formatDivider('=', max)),
            this.CMD.ALIGN_LEFT,
            this.encodeText(this.formatRow('No. Ref', tx.transaction_number || '-', max)),
            this.encodeText(this.formatRow('Waktu', new Date(tx.created_at || Date.now()).toLocaleString('id-ID'), max)),
            this.encodeText(this.formatRow('Status', (tx.status || 'SUKSES').toUpperCase(), max))
        );

        if (tx.reference_number) {
            buffers.push(this.encodeText(this.formatRow('Ref / SN', tx.reference_number, max)));
        }

        buffers.push(this.encodeText(this.formatDivider('-', max)));

        if (tx.service_type === 'BANK_TRANSFER') {
            const principal = parseInt(tx.principal_amount || 0);
            const fee = parseInt(tx.admin_fee || 0);
            const total = parseInt(tx.total_customer_paid || (principal + fee));

            buffers.push(
                this.encodeText(this.formatRow('Akun Agen', tx.account ? tx.account.name : '-', max)),
                this.encodeText(this.formatRow('No. Rek Tujuan', tx.destination_target || '-', max))
            );
            if (tx.destination_holder) {
                buffers.push(this.encodeText(this.formatRow('Nama Penerima', tx.destination_holder, max)));
            }
            buffers.push(
                this.encodeText(this.formatRow('Nominal Pokok', `Rp ${principal.toLocaleString('id-ID')}`, max)),
                this.encodeText(this.formatRow('Biaya Admin', `Rp ${fee.toLocaleString('id-ID')}`, max)),
                this.encodeText(this.formatDivider('-', max)),
                this.CMD.BOLD_ON,
                this.encodeText(this.formatRow('TOTAL DIBAYAR', `Rp ${total.toLocaleString('id-ID')}`, max)),
                this.CMD.BOLD_OFF
            );
        } else if (tx.service_type === 'CASH_WITHDRAWAL') {
            const principal = parseInt(tx.principal_amount || 0);
            const fee = parseInt(tx.admin_fee || 0);

            buffers.push(
                this.encodeText(this.formatRow('Akun Penampung', tx.account ? tx.account.name : '-', max)),
                this.encodeText(this.formatRow('Identitas/EDC', tx.destination_target || '-', max))
            );
            if (tx.destination_holder) {
                buffers.push(this.encodeText(this.formatRow('Nama Nasabah', tx.destination_holder, max)));
            }
            buffers.push(
                this.encodeText(this.formatRow('Nominal Tarik', `Rp ${principal.toLocaleString('id-ID')}`, max)),
                this.encodeText(this.formatRow('Biaya Admin', `Rp ${fee.toLocaleString('id-ID')}`, max)),
                this.encodeText(this.formatDivider('-', max)),
                this.CMD.BOLD_ON,
                this.encodeText(this.formatRow('UANG DITERIMA', `Rp ${principal.toLocaleString('id-ID')}`, max)),
                this.CMD.BOLD_OFF
            );
        } else {
            // PPOB
            const total = parseInt(tx.total_customer_paid || tx.selling_price || 0);
            buffers.push(
                this.encodeText(this.formatRow('Layanan', tx.service_type || 'PPOB', max)),
                this.encodeText(this.formatRow('No. Tujuan', tx.destination_target || '-', max))
            );
            if (tx.reference_number) {
                buffers.push(this.encodeText(this.formatRow('SN / Token', tx.reference_number, max)));
            }
            buffers.push(
                this.encodeText(this.formatDivider('-', max)),
                this.CMD.BOLD_ON,
                this.encodeText(this.formatRow('TOTAL BAYAR', `Rp ${total.toLocaleString('id-ID')}`, max)),
                this.CMD.BOLD_OFF,
                this.encodeText(this.formatRow('Metode', (tx.payment_method || 'CASH').toUpperCase(), max))
            );
        }

        buffers.push(
            this.encodeText(this.formatDivider('=', max)),
            this.CMD.ALIGN_CENTER,
            this.encodeText('Simpan struk ini sebagai bukti sah.\nTerima kasih atas transaksi Anda.\n'),
            this.CMD.PAPER_FEED_5
        );

        const finalBuffer = this.concatBuffers(...buffers);
        await this.sendBuffer(finalBuffer);
    }
}

// Global instance for POS
window.posBtPrinter = new PosBluetoothPrinter();
