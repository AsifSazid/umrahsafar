    // ১. রাইট ক্লিক সম্পূর্ণ বন্ধ করা
    document.addEventListener('contextmenu', event => event.preventDefault());

    // ২. ডেভলপার শর্টকাটগুলো (F12, Ctrl+Shift+I ইত্যাদি) ব্লক করা
    document.onkeydown = function(e) {
        if (e.keyCode == 123) { return false; } // F12
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) { return false; }
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) { return false; }
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) { return false; }
        if (e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) { return false; } // View Source
    };

    // ৩. IVAC-এর মতো অ্যান্টি-ডেবাগার লুপ (ব্রাউজার ফ্রিজ করার জন্য)
    (function() {
        function createDebuggerLoop() {
            function debuggerFunction() {
                // কেউ ইন্সপেক্ট খুললেই এই 'debugger' কোডটি রান করবে এবং সাইট পজ (Pause) হয়ে যাবে
                debugger; 
                setTimeout(debuggerFunction, 100);
            }
            try {
                debuggerFunction();
            } catch (err) {}
        }
        // প্রতি ১০০ মিলি-সেকেন্ড পর পর চেক করবে ইন্সপেক্ট খোলা হয়েছে কিনা
        setInterval(createDebuggerLoop, 100);
    })();