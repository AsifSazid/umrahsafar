/**
 * TravHub PDF Itinerary Generator
 * Uses jsPDF (loaded from CDN) to create a branded itinerary PDF
 * Call: PDFItinerary.generate(bookingData)
 */
const PDFItinerary = (() => {

    function loadJsPDF() {
        return new Promise((resolve, reject) => {
            if (window.jspdf) return resolve(window.jspdf.jsPDF);
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
            script.onload  = () => resolve(window.jspdf.jsPDF);
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    async function generate(data) {
        const jsPDF = await loadJsPDF();
        const doc = new jsPDF({ unit: 'mm', format: 'a4' });
        const W = 210, margin = 20;

        // ── Colors ──
        const green  = [80, 188, 129];   // #50BC81
        const navy   = [26, 32, 57];     // #1A2039
        const dark   = [17, 22, 37];     // #111625
        const white  = [255, 255, 255];
        const muted  = [160, 170, 185];

        // ── Header band ──
        doc.setFillColor(...navy);
        doc.rect(0, 0, W, 40, 'F');

        // Logo area
        doc.setFillColor(...green);
        doc.roundedRect(margin, 10, 18, 18, 3, 3, 'F');
        doc.setTextColor(...white);
        doc.setFontSize(9);
        doc.setFont('helvetica','bold');
        doc.text('TH', margin + 9, 21, { align:'center' });

        // Site name
        doc.setFontSize(18);
        doc.setFont('helvetica','bold');
        doc.setTextColor(...white);
        doc.text('TravHub', margin + 22, 20);
        doc.setTextColor(...green);
        doc.setFontSize(8);
        doc.setFont('helvetica','normal');
        doc.text('UMRAH JOURNEY PLANNER', margin + 22, 26);

        // Booking ref top-right
        doc.setTextColor(...muted);
        doc.setFontSize(8);
        doc.text(data.bookingRef || 'DRAFT', W - margin, 16, { align:'right' });
        doc.setTextColor(...white);
        doc.setFontSize(10);
        doc.text('Umrah Itinerary', W - margin, 24, { align:'right' });

        // ── Title section ──
        doc.setFillColor(...dark);
        doc.rect(0, 40, W, 28, 'F');
        doc.setTextColor(...green);
        doc.setFontSize(20);
        doc.setFont('helvetica','bold');
        doc.text('Umrah Package Summary', margin, 56);
        doc.setTextColor(...muted);
        doc.setFontSize(9);
        doc.text(`Generated: ${new Date().toLocaleDateString('en-BD', { day:'numeric', month:'long', year:'numeric' })}`, margin, 64);

        // ── Info boxes ──
        let y = 80;
        const infoItems = [
            ['Traveler', data.name || 'Not specified'],
            ['Adults / Children', `${data.adults||1} Adult(s), ${data.children||0} Children`],
            ['Package Type', data.packageType || 'Custom Package'],
            ['Flight Type', data.flightType || 'Flexible'],
            ['Duration', `${data.duration||10} Days`],
            ['Makkah Stay', `${data.makkahStay||6} Nights`],
            ['Madinah Stay', `${data.madinahStay||4} Nights`],
            ['Hotel Category', data.hotelCategory || 'Not specified'],
            ['Transport', data.transport || 'Not specified'],
            ['Visa', data.visa || 'Umrah Visa'],
        ];

        doc.setFillColor(30, 38, 72); // navy slightly lighter
        doc.roundedRect(margin, y, W - margin*2, infoItems.length * 9 + 10, 4, 4, 'F');

        doc.setFontSize(9);
        infoItems.forEach(([label, value], i) => {
            const row = y + 8 + i * 9;
            doc.setFont('helvetica','normal');
            doc.setTextColor(...muted);
            doc.text(label + ':', margin + 6, row);
            doc.setFont('helvetica','bold');
            doc.setTextColor(...white);
            doc.text(value, margin + 55, row);
        });

        y += infoItems.length * 9 + 18;

        // ── Pricing ──
        doc.setFillColor(...green);
        doc.roundedRect(margin, y, W - margin*2, 20, 4, 4, 'F');
        doc.setTextColor(...navy);
        doc.setFont('helvetica','bold');
        doc.setFontSize(11);
        doc.text('Estimated Total Price', margin + 6, y + 8);
        doc.setFontSize(14);
        const sym = { SAR:'SR ', USD:'$', BDT:'৳' }[data.currency] || 'SR ';
        doc.text(`${sym}${(data.totalPrice||0).toLocaleString()}`, W - margin - 6, y + 12, { align:'right' });
        doc.setFontSize(8);
        doc.text('(Subject to final confirmation by TravHub agent)', margin + 6, y + 16);
        y += 28;

        // ── Itinerary ──
        if (data.itinerary && data.itinerary.length > 0) {
            doc.setTextColor(...green);
            doc.setFont('helvetica','bold');
            doc.setFontSize(12);
            doc.text('Day-by-Day Itinerary', margin, y);
            y += 8;

            data.itinerary.forEach(item => {
                if (y > 265) { doc.addPage(); y = 20; }
                // Day circle
                doc.setFillColor(...green);
                doc.circle(margin + 4, y + 2, 3.5, 'F');
                doc.setTextColor(...navy);
                doc.setFontSize(7);
                doc.setFont('helvetica','bold');
                doc.text(String(item.day), margin + 4, y + 3, { align:'center' });
                // Day content
                doc.setTextColor(...white);
                doc.setFont('helvetica','bold');
                doc.setFontSize(9);
                doc.text(item.title, margin + 11, y + 2);
                doc.setFont('helvetica','normal');
                doc.setTextColor(...muted);
                doc.setFontSize(8);
                const lines = doc.splitTextToSize(item.desc, W - margin*2 - 14);
                doc.text(lines, margin + 11, y + 7);
                y += 7 + lines.length * 4.5 + 3;
            });
            y += 4;
        }

        // ── Footer ──
        const footerY = 285;
        doc.setDrawColor(...green);
        doc.setLineWidth(0.5);
        doc.line(margin, footerY - 4, W - margin, footerY - 4);
        doc.setTextColor(...muted);
        doc.setFontSize(7);
        doc.setFont('helvetica','normal');
        doc.text('TravHub · info@travhub.com.bd · travhub.com.bd', margin, footerY);
        doc.text('This document is an estimate and not a confirmed booking.', W - margin, footerY, { align:'right' });

        // ── Save ──
        const ref = data.bookingRef || 'Itinerary';
        doc.save(`TravHub-Umrah-${ref}.pdf`);
    }

    return { generate };
})();
