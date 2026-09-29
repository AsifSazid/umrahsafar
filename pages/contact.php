<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle = 'Contact Us | TravHub';
$pageDescription = 'Get in touch with TravHub for Umrah packages, Hajj bookings and travel inquiries from Bangladesh.';
include dirname(__DIR__) . '/includes/header.php';
$csrf = csrfToken();
?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="pt-28 pb-24">
<div class="max-w-7xl mx-auto px-6">

    <!-- Page Header -->
    <div class="text-center mb-16">
        <span class="text-secondary font-bold tracking-widest uppercase text-xs">Get In Touch</span>
        <h1 class="text-4xl lg:text-5xl font-bold mt-2">Contact TravHub</h1>
        <p class="text-white/40 mt-4 max-w-xl mx-auto">Our team is available to help you plan your perfect Umrah journey. Reach out via any channel below.</p>
    </div>

    <div class="grid lg:grid-cols-3 gap-12">

        <!-- Contact Info -->
        <div class="space-y-6">
            <?php
            $contacts = [
                ['icon'=>'phone','label'=>'Phone / WhatsApp','value'=>'+880 1X XX-XXXXXX','href'=>'tel:+8801XXXXXXXXX'],
                ['icon'=>'mail','label'=>'Email','value'=>'info@travhub.com.bd','href'=>'mailto:info@travhub.com.bd'],
                ['icon'=>'map-pin','label'=>'Office','value'=>"Dhaka, Bangladesh\n(By appointment)"],
                ['icon'=>'clock','label'=>'Working Hours','value'=>"Sat–Thu: 9AM – 8PM\nFri: 10AM – 6PM"],
            ];
            foreach ($contacts as $c): ?>
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 flex items-start gap-4">
                <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="<?= $c['icon'] ?>" class="w-5 h-5 text-secondary"></i>
                </div>
                <div>
                    <p class="text-xs text-white/40 uppercase tracking-wider mb-1"><?= $c['label'] ?></p>
                    <?php if (!empty($c['href'])): ?>
                        <a href="<?= $c['href'] ?>" class="font-medium hover:text-secondary transition-colors"><?= nl2br(htmlspecialchars($c['value'])) ?></a>
                    <?php else: ?>
                        <p class="font-medium"><?= nl2br(htmlspecialchars($c['value'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- WhatsApp CTA -->
            <a href="https://wa.me/8801000000000?text=Assalamu+Alaikum%2C+I+would+like+to+inquire+about+Umrah+packages." target="_blank"
               class="flex items-center justify-center gap-3 bg-[#25D366] hover:bg-[#1ebe5c] text-white font-semibold py-4 rounded-2xl transition-all duration-300 w-full">
                <i data-lucide="message-circle" class="w-6 h-6"></i> Chat on WhatsApp
            </a>
        </div>

        <!-- Contact Form -->
        <div class="lg:col-span-2 bg-white/5 border border-white/10 rounded-2xl p-8 lg:p-10">
            <h2 class="text-2xl font-bold mb-8">Send Us a Message</h2>

            <!-- Alert container -->
            <div id="form-alert" class="hidden rounded-xl p-4 mb-6 text-sm font-medium"></div>

            <form id="contact-form" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-sm text-white/60 block mb-2">Full Name *</label>
                        <input type="text" name="name" required placeholder="Your full name" class="w-full bg-white/5 border border-white/10 rounded-xl px-5 py-3.5 focus:border-secondary focus:outline-none transition-colors placeholder-white/20">
                    </div>
                    <div>
                        <label class="text-sm text-white/60 block mb-2">Email Address *</label>
                        <input type="email" name="email" required placeholder="your@email.com" class="w-full bg-white/5 border border-white/10 rounded-xl px-5 py-3.5 focus:border-secondary focus:outline-none transition-colors placeholder-white/20">
                    </div>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-sm text-white/60 block mb-2">Phone / WhatsApp *</label>
                        <input type="tel" name="phone" required placeholder="+880 1X XX-XXXXXX" class="w-full bg-white/5 border border-white/10 rounded-xl px-5 py-3.5 focus:border-secondary focus:outline-none transition-colors placeholder-white/20">
                    </div>
                    <div>
                        <label class="text-sm text-white/60 block mb-2">Subject</label>
                        <select name="subject" class="w-full bg-dark border border-white/10 rounded-xl px-5 py-3.5 focus:border-secondary focus:outline-none transition-colors text-white">
                            <option>Umrah Package Inquiry</option>
                            <option>Hajj Package Inquiry</option>
                            <option>Custom Package Request</option>
                            <option>Hotel Booking</option>
                            <option>Visa Assistance</option>
                            <option>General Question</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-sm text-white/60 block mb-2">Message *</label>
                    <textarea name="message" rows="5" required placeholder="Tell us about your requirements — travel dates, group size, budget, special needs..." class="w-full bg-white/5 border border-white/10 rounded-xl px-5 py-3.5 focus:border-secondary focus:outline-none transition-colors resize-none placeholder-white/20"></textarea>
                </div>
                <button type="submit" id="submit-btn" class="w-full bg-secondary hover:bg-emerald text-primary font-semibold py-4 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 text-base">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    <span id="submit-text">Send Message</span>
                </button>
            </form>
        </div>
    </div>
</div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script>
document.getElementById('contact-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn  = document.getElementById('submit-btn');
    const text = document.getElementById('submit-text');
    const alert = document.getElementById('form-alert');

    btn.disabled = true;
    text.textContent = 'Sending...';
    alert.className = 'hidden';

    try {
        const res  = await fetch(`<?= BASE_URL ?>/api/contact-handler.php`, { method:'POST', body: new FormData(this) });
        const data = await res.json();
        alert.className = `rounded-xl p-4 mb-6 text-sm font-medium ${data.success ? 'bg-secondary/10 border border-secondary/30 text-secondary' : 'bg-red-500/10 border border-red-500/30 text-red-400'}`;
        alert.textContent = data.message;
        alert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        if (data.success) this.reset();
    } catch(err) {
        alert.className = 'rounded-xl p-4 mb-6 text-sm font-medium bg-red-500/10 border border-red-500/30 text-red-400';
        alert.textContent = 'Something went wrong. Please try WhatsApp or call us directly.';
    }

    btn.disabled = false;
    text.textContent = 'Send Message';
});
</script>
</body>
</html>
