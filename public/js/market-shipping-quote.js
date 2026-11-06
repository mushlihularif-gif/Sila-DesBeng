// A delayed quote must never replace a newer address or pickup selection.
globalThis.MarketShippingQuote = class {
    constructor(fetchQuote, changed) { this.fetchQuote = fetchQuote; this.changed = changed; this.revision = 0; }
    async refresh(params) {
        const revision = ++this.revision;
        this.changed({ pending: true, available: false, cost: null, message: 'Menghitung ongkir…' });
        try {
            const result = await this.fetchQuote(params);
            if (revision === this.revision) this.changed({ ...result, pending: false });
        } catch (error) {
            if (revision === this.revision) this.changed({ pending: false, available: false, cost: null, message: error.message || 'Ongkir belum berhasil dimuat. Coba lagi.' });
        }
    }
};
