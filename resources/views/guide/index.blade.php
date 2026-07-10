@extends('layouts.app')
@section('title', 'POS Guide')
@section('page-title', 'POS Guide')

@push('styles')
<style>
.guide-lang-btn { border-radius: 20px; font-size: .82rem; font-weight: 600; padding: .35rem .95rem; border: 2px solid transparent; transition: all .2s; cursor: pointer; background: transparent; }
.guide-lang-btn.active { background: #c8a014; border-color: #c8a014; color: #000; }
.guide-lang-btn:not(.active) { border-color: rgba(200,160,20,.5); color: #c8a014; }
.guide-lang-btn:hover:not(.active) { background: rgba(200,160,20,.12); }
.intro-card { background: linear-gradient(135deg,#1a1a0a,#2a2000); border-radius: 14px; padding: 1.5rem 1.75rem; margin-bottom: 1.5rem; color: #fff; }
.intro-card .intro-title { font-size: 1.15rem; font-weight: 700; color: #e8c040; margin-bottom: .45rem; }
.intro-card .intro-text  { font-size: .875rem; opacity: .82; line-height: 1.7; margin: 0; }
.qn-card { border-radius: 12px; border: 1px solid #eef0f7; background: #fff; padding: .85rem 1rem; display: flex; align-items: center; gap: .75rem; cursor: pointer; transition: all .2s; text-decoration: none; color: inherit; box-shadow: 0 1px 6px rgba(0,0,0,.05); }
.qn-card:hover { border-color: #c8a014; box-shadow: 0 4px 16px rgba(200,160,20,.15); transform: translateY(-1px); color: inherit; }
.qn-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.05rem; color: #fff; flex-shrink: 0; }
.qn-label { font-size: .82rem; font-weight: 600; color: #1a1f36; }
.g-card { border-radius: 14px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,.07); margin-bottom: 1.25rem; overflow: hidden; background: #fff; }
.g-card-header { padding: 1rem 1.25rem; display: flex; align-items: center; gap: .8rem; cursor: pointer; user-select: none; border-bottom: 1px solid #f5f3ee; }
.g-card-header:hover { background: #fdfbf6; }
.sec-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #fff; flex-shrink: 0; }
.sec-title { font-size: 1rem; font-weight: 700; margin: 0; color: #1a1f36; }
.sec-desc  { font-size: .77rem; color: #9ca3af; margin: 0; }
.chev { margin-left: auto; color: #bbb; font-size: 1rem; transition: transform .22s; }
.collapsed .chev { transform: rotate(-90deg); }
.collapsed .g-card-body { display: none; }
.g-card-body { padding: 0 1.25rem 1.25rem; }
.guide-steps { list-style: none; padding: 0; margin: .75rem 0 0; counter-reset: gs; }
.guide-steps li { counter-increment: gs; display: flex; align-items: flex-start; gap: .75rem; padding: .5rem 0; border-bottom: 1px solid #f5f3ee; font-size: .875rem; color: #374151; line-height: 1.6; }
.guide-steps li:last-child { border-bottom: none; }
.guide-steps li::before { content: counter(gs); background: #fdf9ee; border: 2px solid #e8c040; color: #8a6a08; min-width: 24px; height: 24px; border-radius: 6px; font-size: .7rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px; }
.tip-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: .75rem 1rem; margin-top: .85rem; }
.tip-box .tip-hdr { font-size: .7rem; font-weight: 700; color: #92400e; text-transform: uppercase; letter-spacing: .5px; margin-bottom: .35rem; }
.tip-box ul { margin: 0; padding-left: 1.2rem; }
.tip-box ul li { font-size: .83rem; color: #78350f; line-height: 1.6; padding: .1rem 0; }
.sec-hdr-label { font-size: .7rem; font-weight: 800; letter-spacing: 1.2px; text-transform: uppercase; color: #9ca3af; margin-bottom: .65rem; display: flex; align-items: center; gap: 6px; }
.sec-hdr-label::before { content: ''; width: 3px; height: 12px; background: #c8a014; border-radius: 2px; }
@media (max-width:575px) { .guide-lang-btn { font-size: .74rem; padding: .28rem .7rem; } .sec-title { font-size: .92rem; } }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
    <div>
        <h5 class="mb-0 fw-bold" id="g-title"></h5>
        <div class="text-muted small mt-1" id="g-subtitle"></div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="text-muted small"><i class="bi bi-translate me-1"></i></span>
        <button class="guide-lang-btn" onclick="setLang('en')" data-l="en">English</button>
        <button class="guide-lang-btn" onclick="setLang('hi')" data-l="hi">हिंदी</button>
        <button class="guide-lang-btn" onclick="setLang('gu')" data-l="gu">ગુજરાતી</button>
    </div>
</div>

<div class="intro-card">
    <div class="intro-title" id="g-intro-title"></div>
    <p class="intro-text" id="g-intro-text"></p>
</div>

<div class="sec-hdr-label mb-2" id="g-nav-label"></div>
<div class="row g-2 mb-4" id="g-quicknav"></div>

<div id="g-sections"></div>

@endsection

@push('scripts')
<script>
const G = {
en: {
  title: "POS System Guide",
  subtitle: "Complete step-by-step guide for Mayank Footware POS",
  intro_title: "Welcome to the POS Guide",
  intro_text: "This guide explains every feature of the system with numbered steps. Use the quick-jump cards below to go directly to any topic. Select हिंदी or ગુજરાતી above to read in your language.",
  nav_label: "Jump to a Topic",
  sections: [
    { id:"dashboard", icon:"bi-speedometer2", bg:"linear-gradient(135deg,#e74c3c,#c0392b)", nav:"Dashboard",
      title:"Dashboard", desc:"Your store's real-time performance at a glance",
      steps:[
        "After login, the <strong>Dashboard</strong> is the first screen — it summarises your store's activity.",
        "<strong>Today's KPI Cards</strong>: Total Sales, Cash Collected, UPI/Card payments (only visible with <em>view sales</em> permission).",
        "<strong>Monthly Snapshot</strong>: Cumulative sales, purchases, and expenses for the current month.",
        "<strong>Recent Sales Table</strong>: Last 8 bills — invoice, customer, amount, payment method, time.",
        "<strong>Stock Alerts Widget</strong>: Products that are out of stock or below the alert level.",
        "<strong>Analytics</strong>: Top products, brands, and cashier performance (requires <em>view reports</em> permission)."
      ],
      tips:[
        "Revenue and sales data are only visible to users who have the <em>view sales</em> permission.",
        "When you see a low-stock alert, go to <strong>Inventory → Add Stock</strong> immediately."
      ]
    },
    { id:"pos", icon:"bi-cart3", bg:"linear-gradient(135deg,#3498db,#2471a3)", nav:"New Sale",
      title:"New Sale (POS)", desc:"Create a bill and process customer payment",
      steps:[
        "Click <strong>New Sale / POS</strong> in the sidebar or the \'New Sale\' button on the dashboard.",
        "In the <strong>search bar</strong>, scan a barcode with a scanner OR type the Article Code / Product Name and press <kbd>Enter</kbd>.",
        "If an <strong>exact match</strong> is found (barcode or article code), the item is added to cart instantly.",
        "For partial matches, a dropdown appears — click the correct product to add it.",
        "Use <strong>+</strong> / <strong>−</strong> in the cart, or type directly in the quantity box.",
        "To apply a <strong>per-item discount</strong>, click the edit icon next to the item.",
        "(Optional) Enter the <strong>customer name and mobile</strong> for records.",
        "Enter an overall <strong>Bill Discount</strong> if applicable.",
        "Enter the <strong>Paid Amount</strong> — the system calculates change automatically.",
        "Select <strong>Payment Method</strong>: Cash, UPI, Card, or Other.",
        "Click <strong>Complete Sale</strong> — the bill is saved and the receipt appears.",
        "Click <strong>Print Receipt</strong> to print, or start a new sale."
      ],
      tips:[
        "A barcode scanner speeds up billing — one scan adds the product instantly.",
        "You can add multiple products before completing the sale.",
        "If the customer overpays, the system shows the <em>change due</em> automatically."
      ]
    },
    { id:"products", icon:"bi-box-seam-fill", bg:"linear-gradient(135deg,#27ae60,#1e8449)", nav:"Products",
      title:"Products", desc:"Add, edit, and manage your footwear catalog",
      steps:[
        "Go to <strong>Inventory → Products</strong> in the sidebar.",
        "Click <strong>Add Product</strong> to create a new product.",
        "Enter: <strong>Product Name</strong>, Category, Brand.",
        "Enter the <strong>Article Code (SKU)</strong> — a unique code like <em>NK-001</em> used for fast POS search.",
        "(Optional) Enter an <strong>Item Code</strong> — a secondary internal reference number.",
        "Enter the <strong>Selling Price (MRP)</strong> — what the customer pays.",
        "Enter the <strong>Purchase Price</strong> (visible only with <em>view purchase price</em> permission).",
        "Set the <strong>Stock Alert Level</strong> — system warns when stock reaches this number.",
        "Keep the product <strong>Active</strong> so it appears in POS search.",
        "Click <strong>Save</strong>."
      ],
      tips:[
        "Article Code (SKU) must be unique — use it for quick lookup in POS and stock screens.",
        "Barcode labels can be printed from the product detail page.",
        "Click <strong>Edit</strong> on the Products list to update prices anytime."
      ]
    },
    { id:"stock", icon:"bi-plus-square-fill", bg:"linear-gradient(135deg,#9b59b6,#7d3c98)", nav:"Add Stock",
      title:"Add Stock", desc:"Record incoming goods and update inventory",
      steps:[
        "Go to <strong>Inventory → Add Stock</strong> in the sidebar.",
        "Scan the barcode or type the Article Code / Product Name in the search bar.",
        "Click the correct product from the search results.",
        "The product card shows: current stock, alert level, and last purchase price.",
        "Enter the <strong>Quantity</strong> received (number of pairs/units).",
        "Enter the <strong>Purchase Price per unit</strong> (if you have permission).",
        "Click <strong>Add Stock</strong> — inventory is updated instantly and recorded in history."
      ],
      tips:[
        "Always record stock when new goods arrive to keep POS counts accurate.",
        "View all stock changes under <strong>Inventory → Stock History</strong>."
      ]
    },
    { id:"history", icon:"bi-receipt", bg:"linear-gradient(135deg,#f39c12,#d68910)", nav:"Sales History",
      title:"Sales History", desc:"View, search, and review all past transactions",
      steps:[
        "Go to <strong>Sales → Sales History</strong> in the sidebar (requires <em>view sales</em> permission).",
        "The list shows all completed sales, newest first.",
        "Use the <strong>Date</strong> filter to see sales for a specific day.",
        "Use the <strong>Search</strong> box to find by invoice number, customer name, or mobile.",
        "Click any <strong>Invoice Number</strong> to view full details — items, discount, payment method.",
        "On the detail page, click <strong>Print Receipt</strong> to reprint the bill."
      ],
      tips:[
        "Only users with <em>view sales</em> permission can access this page.",
        "Use the date filter daily to reconcile cash collections."
      ]
    },
    @if(\App\Helpers\Module::anyReportActive())
    { id:"reports", icon:"bi-graph-up-arrow", bg:"linear-gradient(135deg,#1abc9c,#148f77)", nav:"Reports",
      title:"Reports", desc:"Analyse business performance with detailed reports",
      steps:[
        "Go to the <strong>Reports</strong> section in the sidebar (requires <em>view reports</em> permission).",
        @if(\App\Helpers\Module::isActive('report_daily'))
        "<strong>Daily Report</strong>: Day-wise sales totals and payment method breakdown.",
        @endif
        @if(\App\Helpers\Module::isActive('report_monthly'))
        "<strong>Monthly Report</strong>: Month-by-month sales comparison.",
        @endif
        @if(\App\Helpers\Module::isActive('report_inventory'))
        "<strong>Inventory Report</strong>: Current stock levels, stock value, and low-stock items.",
        @endif
        @if(\App\Helpers\Module::isActive('report_profit_loss'))
        "<strong>Profit & Loss</strong>: Revenue vs purchase cost vs expenses — actual profit.",
        @endif
        @if(\App\Helpers\Module::isActive('report_gst'))
        "<strong>GST Report</strong>: Tax collected on sales — use for tax filing.",
        @endif
        @if(\App\Helpers\Module::isActive('report_cashier'))
        "<strong>Cashier Report</strong>: Individual sales performance per staff member.",
        @endif
        @if(\App\Helpers\Module::isActive('report_stock_movement'))
        "<strong>Stock Movement</strong>: Complete history of all stock additions and adjustments.",
        @endif
        "Press <strong>Ctrl+P</strong> in the browser to print or save any report as PDF."
      ],
      tips:[
        "Keep purchase prices and expenses updated for accurate Profit & Loss.",
        "Purchase price data is only shown to users with <em>view purchase price</em> permission."
      ]
    },
    @endif
    { id:"users", icon:"bi-shield-lock-fill", bg:"linear-gradient(135deg,#6f42c1,#5a32a3)", nav:"Users & Roles",
      title:"Users & Roles", desc:"Manage staff accounts and control access",
      steps:[
        "Go to <strong>Administration → Users</strong> (Admin or Super Admin required).",
        "Click <strong>Add User</strong> to create a new staff account.",
        "Enter Name, Email, Password, and select a <strong>Role</strong>.",
        "<strong>Super Admin</strong>: Full access including permissions setup — owner/developer only.",
        "<strong>Admin</strong>: Full access, can manage users and settings.",
        "<strong>Manager</strong>: Inventory, sales, reports — but not user management.",
        "<strong>Cashier</strong>: POS billing and stock viewing only.",
        "For custom access, go to <strong>Custom Roles</strong> to create a role with specific permissions.",
        "Use the <strong>Permissions</strong> matrix to toggle access per role."
      ],
      tips:[
        "Assign Cashier role to counter staff — they can only bill, not view reports or prices.",
        "Use Custom Roles when a staff member needs access between Cashier and Manager level."
      ]
    },
    { id:"settings", icon:"bi-gear-fill", bg:"linear-gradient(135deg,#636e72,#2d3436)", nav:"Settings",
      title:"Settings", desc:"Configure shop details, receipts, and modules",
      steps:[
        "Go to <strong>Administration → Settings</strong> (Admin access required).",
        "Update <strong>Shop Name, Address, Phone Number</strong> — printed on every receipt.",
        "Upload your <strong>Shop Logo</strong> — shown on receipts and in the sidebar.",
        "Enter your <strong>GST Number</strong> if applicable.",
        "In the <strong>Modules</strong> section, enable or disable: Sales History, Purchases, Expenses, Customers, Barcodes, Reports, Sale Returns.",
        "Click <strong>Save Settings</strong> to apply all changes."
      ],
      tips:[
        "Disabling a module hides it from the sidebar for all users.",
        "Always verify shop name and address before printing customer receipts."
      ]
    }
  ]
},

hi: {
  title: "POS सिस्टम गाइड",
  subtitle: "Mayank Footware POS की पूरी जानकारी हिंदी में",
  intro_title: "POS गाइड में आपका स्वागत है",
  intro_text: "इस गाइड में सिस्टम की हर सुविधा को step-by-step समझाया गया है। नीचे दिए quick-jump cards से सीधे किसी topic पर जाएं। ऊपर अपनी पसंद की भाषा चुनें।",
  nav_label: "सीधे किसी विषय पर जाएं",
  sections: [
    { id:"dashboard", icon:"bi-speedometer2", bg:"linear-gradient(135deg,#e74c3c,#c0392b)", nav:"डैशबोर्ड",
      title:"डैशबोर्ड", desc:"आपकी दुकान का आज का performance एक नज़र में",
      steps:[
        "Login के बाद सबसे पहले <strong>Dashboard</strong> खुलता है — दुकान की सभी जरूरी जानकारी यहाँ दिखती है।",
        "<strong>आज के KPI Cards</strong>: कुल बिक्री, नकद संग्रह, UPI/Card payment (सिर्फ <em>view sales</em> permission वाले देख सकते हैं)।",
        "<strong>मासिक सारांश</strong>: इस महीने की कुल बिक्री, खरीद, और खर्च।",
        "<strong>हाल की बिक्री</strong>: आखिरी 8 bills — invoice number, customer, amount, payment method, समय।",
        "<strong>Stock Alerts</strong>: खत्म हो रहे या alert level से नीचे आ गए products की list।",
        "<strong>Analytics</strong>: Top products, brands, और cashier performance (<em>view reports</em> permission जरूरी)।"
      ],
      tips:[
        "Sales की जानकारी सिर्फ <em>view sales</em> permission वाले users देख सकते हैं।",
        "Stock alert दिखे तो <strong>Inventory → Add Stock</strong> से stock add करें।"
      ]
    },
    { id:"pos", icon:"bi-cart3", bg:"linear-gradient(135deg,#3498db,#2471a3)", nav:"नई बिक्री",
      title:"नई बिक्री (POS)", desc:"Bill बनाएं और customer का payment process करें",
      steps:[
        "Sidebar में <strong>New Sale / POS</strong> click करें या dashboard पर 'New Sale' button दबाएं।",
        "<strong>Search bar</strong> में barcode scan करें या Article Code / Product Name type करके <kbd>Enter</kbd> दबाएं।",
        "<strong>Exact match</strong> मिलने पर (barcode या article code) product automatically cart में add हो जाता है।",
        "Partial match पर list आती है — सही product click करके cart में add करें।",
        "Cart में <strong>+</strong> / <strong>−</strong> से या quantity box में directly type करके quantity बदलें।",
        "<strong>Per-item discount</strong> देने के लिए item के edit icon पर click करें।",
        "(Optional) <strong>Customer का नाम और mobile</strong> enter करें — record के लिए।",
        "पूरे bill पर <strong>Bill Discount</strong> enter करें (अगर हो)।",
        "<strong>Paid Amount</strong> enter करें — change automatic calculate होगा।",
        "<strong>Payment Method</strong> चुनें: Cash, UPI, Card, या Other।",
        "<strong>Complete Sale</strong> click करें — bill save होगा और receipt दिखेगी।",
        "<strong>Print Receipt</strong> से receipt print करें।"
      ],
      tips:[
        "Barcode scanner से billing बहुत तेज होती है — एक scan में product add।",
        "Sale complete करने से पहले जितने चाहें products add कर सकते हैं।",
        "Customer ज्यादा पैसे दे तो system <em>change due</em> automatically बताएगा।"
      ]
    },
    { id:"products", icon:"bi-box-seam-fill", bg:"linear-gradient(135deg,#27ae60,#1e8449)", nav:"Products",
      title:"Products", desc:"अपने footwear catalog को add, edit, और manage करें",
      steps:[
        "Sidebar में <strong>Inventory → Products</strong> पर जाएं।",
        "<strong>Add Product</strong> click करके नया product बनाएं।",
        "भरें: <strong>Product Name</strong>, Category, Brand।",
        "<strong>Article Code (SKU)</strong> enter करें — unique code जैसे <em>NK-001</em>, POS में fast search के लिए।",
        "(Optional) <strong>Item Code</strong> — एक अतिरिक्त internal reference number।",
        "<strong>Selling Price (MRP)</strong> enter करें — customer को यही price दिखेगी।",
        "<strong>Purchase Price</strong> enter करें (सिर्फ <em>view purchase price</em> permission वाले)।",
        "<strong>Stock Alert Level</strong> set करें — stock इस level पर आने पर system alert देगा।",
        "Product को <strong>Active</strong> रखें ताकि POS में दिखे।",
        "<strong>Save</strong> click करें।"
      ],
      tips:[
        "Article Code (SKU) unique होना जरूरी है — POS और stock में fast search के लिए।",
        "Product detail page से barcode label print कर सकते हैं।",
        "Price update करने के लिए Products list में <strong>Edit</strong> click करें।"
      ]
    },
    { id:"stock", icon:"bi-plus-square-fill", bg:"linear-gradient(135deg,#9b59b6,#7d3c98)", nav:"Stock Add",
      title:"Stock Add करें", desc:"आने वाला stock record करें और inventory update करें",
      steps:[
        "Sidebar में <strong>Inventory → Add Stock</strong> पर जाएं।",
        "Search bar में barcode scan करें या Article Code / Product Name type करें।",
        "Result से सही product click करें।",
        "Product card में current stock, alert level, और last purchase price दिखेगी।",
        "<strong>Quantity</strong> enter करें (कितने pairs/units मिले हैं)।",
        "<strong>Purchase Price per unit</strong> enter करें (अगर permission हो)।",
        "<strong>Add Stock</strong> click करें — stock तुरंत update होगा और history में record होगा।"
      ],
      tips:[
        "नया माल आने पर हमेशा stock add करें ताकि POS में सही count दिखे।",
        "<strong>Inventory → Stock History</strong> से सभी stock changes देख सकते हैं।"
      ]
    },
    { id:"history", icon:"bi-receipt", bg:"linear-gradient(135deg,#f39c12,#d68910)", nav:"Sales History",
      title:"Sales History", desc:"सभी पुरानी बिक्री देखें, खोजें, और review करें",
      steps:[
        "Sidebar में <strong>Sales → Sales History</strong> पर जाएं (<em>view sales</em> permission जरूरी)।",
        "सभी completed sales नई से पुरानी क्रम में दिखती हैं।",
        "<strong>Date</strong> filter से किसी specific दिन की sales देखें।",
        "<strong>Search</strong> box में invoice number, customer name, या mobile से खोजें।",
        "किसी भी <strong>Invoice Number</strong> पर click करके पूरी details देखें।",
        "Detail page पर <strong>Print Receipt</strong> से bill reprint करें।"
      ],
      tips:[
        "Sales History सिर्फ <em>view sales</em> permission वाले users देख सकते हैं।",
        "Date filter से रोज का cash collection check करें।"
      ]
    },
    @if(\App\Helpers\Module::anyReportActive())
    { id:"reports", icon:"bi-graph-up-arrow", bg:"linear-gradient(135deg,#1abc9c,#148f77)", nav:"Reports",
      title:"Reports", desc:"Detailed reports से business performance analyze करें",
      steps:[
        "Sidebar में <strong>Reports</strong> section पर जाएं (<em>view reports</em> permission जरूरी)।",
        @if(\App\Helpers\Module::isActive('report_daily'))
        "<strong>Daily Report</strong>: हर दिन की sales total और payment method breakdown।",
        @endif
        @if(\App\Helpers\Module::isActive('report_monthly'))
        "<strong>Monthly Report</strong>: महीने-दर-महीने sales comparison।",
        @endif
        @if(\App\Helpers\Module::isActive('report_inventory'))
        "<strong>Inventory Report</strong>: मौजूदा stock levels, stock value, और low-stock items।",
        @endif
        @if(\App\Helpers\Module::isActive('report_profit_loss'))
        "<strong>Profit & Loss</strong>: Revenue vs purchase cost vs expenses — असली मुनाफा।",
        @endif
        @if(\App\Helpers\Module::isActive('report_gst'))
        "<strong>GST Report</strong>: Sales पर collect किया गया tax — tax filing के लिए।",
        @endif
        @if(\App\Helpers\Module::isActive('report_cashier'))
        "<strong>Cashier Report</strong>: हर staff member की individual sales performance।",
        @endif
        @if(\App\Helpers\Module::isActive('report_stock_movement'))
        "<strong>Stock Movement</strong>: सभी stock changes का पूरा इतिहास।",
        @endif
        "Browser का <strong>Ctrl+P</strong> use करके कोई भी report print या PDF save करें।"
      ],
      tips:[
        "Profit & Loss सही देखने के लिए purchase prices और expenses हमेशा update रखें।",
        "Purchase price data सिर्फ <em>view purchase price</em> permission वाले देख सकते हैं।"
      ]
    },
    @endif
    { id:"users", icon:"bi-shield-lock-fill", bg:"linear-gradient(135deg,#6f42c1,#5a32a3)", nav:"Users & Roles",
      title:"Users & Roles", desc:"Staff accounts manage करें और access control करें",
      steps:[
        "Sidebar में <strong>Administration → Users</strong> पर जाएं (Admin या Super Admin जरूरी)।",
        "<strong>Add User</strong> click करके नया staff account बनाएं।",
        "Name, Email, Password enter करें और <strong>Role</strong> select करें।",
        "<strong>Super Admin</strong>: पूरा access + permissions setup — सिर्फ owner के लिए।",
        "<strong>Admin</strong>: पूरा access, users और settings manage कर सकते हैं।",
        "<strong>Manager</strong>: Inventory manage, sales देखना, reports access।",
        "<strong>Cashier</strong>: सिर्फ POS billing और stock देखना।",
        "Custom access के लिए <strong>Custom Roles</strong> में role बनाएं और specific permissions दें।",
        "<strong>Permissions</strong> matrix से किसी role की access toggle करें।"
      ],
      tips:[
        "Counter staff को Cashier role दें — वो सिर्फ billing कर सकते हैं, reports नहीं।",
        "Cashier और Manager के बीच का access चाहिए तो Custom Role use करें।"
      ]
    },
    { id:"settings", icon:"bi-gear-fill", bg:"linear-gradient(135deg,#636e72,#2d3436)", nav:"Settings",
      title:"Settings", desc:"दुकान की details, receipt, और modules configure करें",
      steps:[
        "Sidebar में <strong>Administration → Settings</strong> पर जाएं (Admin access जरूरी)।",
        "<strong>Shop Name, Address, Phone Number</strong> update करें — हर receipt पर दिखेगा।",
        "<strong>Shop Logo</strong> upload करें — receipt और sidebar पर show होगा।",
        "<strong>GST Number</strong> enter करें (अगर applicable हो)।",
        "<strong>Modules</strong> section में features enable/disable करें: Sales History, Purchases, Expenses, Customers, Barcodes, Reports, Sale Returns।",
        "<strong>Save Settings</strong> click करके changes apply करें।"
      ],
      tips:[
        "Module disable करने पर वो feature सभी users के लिए sidebar से hide हो जाता है।",
        "Customer को receipt देने से पहले shop name और address जरूर check करें।"
      ]
    }
  ]
},

gu: {
  title: "POS સિસ્ટમ ગाઇड",
  subtitle: "Mayank Footware POS ની સંપૂર્ણ માહિતી ગુજરાતીમાં",
  intro_title: "POS ગाઇडमां आपनुं स्वागत छे",
  intro_text: "आ guide मां system नी दरेक सुविधा step-by-step समजावी छे. नीचे आपेला quick-jump cards थी सीधा कोई पण topic पर जाओ. ऊपर तमारी भाषा पसंद करो.",
  nav_label: "સीधा कोई विषय पर जाओ",
  sections: [
    { id:"dashboard", icon:"bi-speedometer2", bg:"linear-gradient(135deg,#e74c3c,#c0392b)", nav:"ડॅशबोर्ड",
      title:"ડॅशबોर્ड", desc:"तमारी दुकान नो आजनो performance एक नजरमां",
      steps:[
        "Login कर्या पछी पहेलुं <strong>Dashboard</strong> ख़ूले छे — दुकान नी बधी जरूरी माहिती अहीं देखाय छे.",
        "<strong>आजना KPI Cards</strong>: कुल वेचाण, रोकड संग्रह, UPI/Card payment (फक्त <em>view sales</em> permission वाळा जोई शके).",
        "<strong>मासिक सारांश</strong>: आ महिनानुं कुल वेचाण, खरीद, अने खर्च.",
        "<strong>ताजेतरना वेचाण</strong>: छेल्ला 8 bills — invoice, customer, amount, payment method, समय.",
        "<strong>Stock Alerts</strong>: जे products खूटी ग़या छे या alert level करतां ओछा छे तेनी list.",
        "<strong>Analytics</strong>: Top products, brands, अने cashier performance (<em>view reports</em> permission जरूरी)."
      ],
      tips:[
        "Sales नी माहिती फक्त <em>view sales</em> permission धरावता users जोई शके.",
        "Stock alert देखाय तो <strong>Inventory → Add Stock</strong> थी तरत stock ऊमेरो."
      ]
    },
    { id:"pos", icon:"bi-cart3", bg:"linear-gradient(135deg,#3498db,#2471a3)", nav:"नवुं वेचाण",
      title:"નвуं вेچाण (POS)", desc:"Bill बनावो अने customer नुं payment process करो",
      steps:[
        "Sidebar मां <strong>New Sale / POS</strong> click करो अथवा dashboard नो 'New Sale' button दबावो.",
        "<strong>Search bar</strong> मां barcode scan करो अथवा Article Code / Product Name type करीने <kbd>Enter</kbd> दबावो.",
        "<strong>Exact match</strong> मळे (barcode अथवा article code) त्यारे product automatically cart मां add थाय.",
        "Partial match पर list आवे — सही product click करीने cart मां add करो.",
        "Cart मां <strong>+</strong> / <strong>−</strong> वापरो, अथवा quantity box मां directly type करो.",
        "<strong>Per-item discount</strong> आपवा item नो edit icon click करो.",
        "(Optional) <strong>Customer नुं नाम अने mobile</strong> enter करो — record माते.",
        "पूरा bill पर <strong>Bill Discount</strong> enter करो (जो होय तो).",
        "<strong>Paid Amount</strong> enter करो — change आपोआप calculate थशे.",
        "<strong>Payment Method</strong> पसंद करो: Cash, UPI, Card, अथवा Other.",
        "<strong>Complete Sale</strong> click करो — bill save थशे अने receipt देखाशे.",
        "<strong>Print Receipt</strong> थी receipt print करो."
      ],
      tips:[
        "Barcode scanner थी billing खूब fast थाय — एक scan मां product add.",
        "Sale complete करतां पहेलां जेटला जोईए तेटला products add करी शकाय.",
        "Customer वधारे पैसा आपे तो system <em>change due</em> आपोआप बताशे."
      ]
    },
    { id:"products", icon:"bi-box-seam-fill", bg:"linear-gradient(135deg,#27ae60,#1e8449)", nav:"Products",
      title:"Products", desc:"Footwear catalog ने add, edit अने manage करो",
      steps:[
        "Sidebar मां <strong>Inventory → Products</strong> पर जाओ.",
        "<strong>Add Product</strong> click करीने नवो product बनावो.",
        "भरो: <strong>Product Name</strong>, Category, Brand.",
        "<strong>Article Code (SKU)</strong> enter करो — unique code जेवो के <em>NK-001</em>, POS मां fast search माते.",
        "(Optional) <strong>Item Code</strong> — वधारानो internal reference number.",
        "<strong>Selling Price (MRP)</strong> enter करो — customer ने आ price देखाशे.",
        "<strong>Purchase Price</strong> enter करो (फक्त <em>view purchase price</em> permission वाळा).",
        "<strong>Stock Alert Level</strong> set करो — stock आ level पर आवे त्यारे alert मळशे.",
        "Product ने <strong>Active</strong> राखो जेथी POS मां देखाय.",
        "<strong>Save</strong> click करो."
      ],
      tips:[
        "Article Code (SKU) unique होवो जोईए — POS अने stock मां fast search माते.",
        "Product detail page मांथी barcode label print करी शकाय.",
        "Price update करवा Products list मां <strong>Edit</strong> click करो."
      ]
    },
    { id:"stock", icon:"bi-plus-square-fill", bg:"linear-gradient(135deg,#9b59b6,#7d3c98)", nav:"Stock ऊमेरो",
      title:"Stock ऊमेरो", desc:"आवतो माल record करो अने inventory update करो",
      steps:[
        "Sidebar मां <strong>Inventory → Add Stock</strong> पर जाओ.",
        "Search bar मां barcode scan करो या Article Code / Product Name type करो.",
        "Search result मांथी सही product click करो.",
        "Product card मां current stock, alert level, अने last purchase price देखाशे.",
        "<strong>Quantity</strong> enter करो (केटला pairs/units मळ्या).",
        "<strong>Purchase Price per unit</strong> enter करो (जो permission होय).",
        "<strong>Add Stock</strong> click करो — stock तरत update थशे अने history मां record थशे."
      ],
      tips:[
        "नवो माल आवे त्यारे हमेशा stock ऊमेरो जेथी POS मां सही count देखाय.",
        "<strong>Inventory → Stock History</strong> मांथी बधा stock changes जोई शकाय."
      ]
    },
    { id:"history", icon:"bi-receipt", bg:"linear-gradient(135deg,#f39c12,#d68910)", nav:"Sales History",
      title:"Sales History", desc:"बधां जूनां वेचाण जुओ, शोधो अने review करो",
      steps:[
        "Sidebar मां <strong>Sales → Sales History</strong> पर जाओ (<em>view sales</em> permission जरूरी).",
        "बधां completed sales नवीथी जूनी क्रममां देखाय.",
        "<strong>Date</strong> filter थी कोई specific दिवस नां वेचाण जुओ.",
        "<strong>Search</strong> box मां invoice number, customer name, या mobile थी शोधो.",
        "कोई पण <strong>Invoice Number</strong> पर click करीने पूरी details जुओ.",
        "Detail page पर <strong>Print Receipt</strong> थी bill reprint करो."
      ],
      tips:[
        "Sales History फक्त <em>view sales</em> permission वाळा users जोई शके.",
        "Date filter थी रोजनुं cash collection check करो."
      ]
    },
    @if(\App\Helpers\Module::anyReportActive())
    { id:"reports", icon:"bi-graph-up-arrow", bg:"linear-gradient(135deg,#1abc9c,#148f77)", nav:"Reports",
      title:"Reports", desc:"Detailed reports थी business performance analyze करो",
      steps:[
        "Sidebar मां <strong>Reports</strong> section पर जाओ (<em>view reports</em> permission जरूरी).",
        @if(\App\Helpers\Module::isActive('report_daily'))
        "<strong>Daily Report</strong>: दररोज नुं sales total अने payment method breakdown.",
        @endif
        @if(\App\Helpers\Module::isActive('report_monthly'))
        "<strong>Monthly Report</strong>: महिना-दर-महिना sales comparison.",
        @endif
        @if(\App\Helpers\Module::isActive('report_inventory'))
        "<strong>Inventory Report</strong>: हालना stock levels, stock value, अने low-stock items.",
        @endif
        @if(\App\Helpers\Module::isActive('report_profit_loss'))
        "<strong>Profit & Loss</strong>: Revenue vs purchase cost vs expenses — असली नफो.",
        @endif
        @if(\App\Helpers\Module::isActive('report_gst'))
        "<strong>GST Report</strong>: Sales पर collect थयेलो tax — tax filing माते.",
        @endif
        @if(\App\Helpers\Module::isActive('report_cashier'))
        "<strong>Cashier Report</strong>: दरेक staff member नां individual sales.",
        @endif
        @if(\App\Helpers\Module::isActive('report_stock_movement'))
        "<strong>Stock Movement</strong>: बधां stock changes नो पूरो इतिहास.",
        @endif
        "Browser नो <strong>Ctrl+P</strong> वापरीने कोई पण report print या PDF save करो."
      ],
      tips:[
        "Profit & Loss सही जोवा माते purchase prices अने expenses हमेशा update राखो.",
        "Purchase price data फक्त <em>view purchase price</em> permission वाळा जोई शके."
      ]
    },
    @endif
    { id:"users", icon:"bi-shield-lock-fill", bg:"linear-gradient(135deg,#6f42c1,#5a32a3)", nav:"Users & Roles",
      title:"Users & Roles", desc:"Staff accounts manage करो अने access control करो",
      steps:[
        "Sidebar मां <strong>Administration → Users</strong> पर जाओ (Admin या Super Admin जरूरी).",
        "<strong>Add User</strong> click करीने नवुं staff account बनावो.",
        "Name, Email, Password enter करो अने <strong>Role</strong> select करो.",
        "<strong>Super Admin</strong>: पूर्ण access + permissions setup — फक्त owner माते.",
        "<strong>Admin</strong>: पूर्ण access, users अने settings manage करी शकाय.",
        "<strong>Manager</strong>: Inventory manage, sales जोवुं, reports access.",
        "<strong>Cashier</strong>: फक्त POS billing अने stock जोवुं.",
        "Custom access माते <strong>Custom Roles</strong> मां role बनावो अने specific permissions आपो.",
        "<strong>Permissions</strong> matrix थी कोई role नी access toggle करो."
      ],
      tips:[
        "Counter staff ने Cashier role आपो — ते फक्त billing करी शके, reports नहीं.",
        "Cashier अने Manager वाचे नो access जोईए तो Custom Role वापरो."
      ]
    },
    { id:"settings", icon:"bi-gear-fill", bg:"linear-gradient(135deg,#636e72,#2d3436)", nav:"Settings",
      title:"Settings", desc:"दुकान नी details, receipt अने modules configure करो",
      steps:[
        "Sidebar मां <strong>Administration → Settings</strong> पर जाओ (Admin access जरूरी).",
        "<strong>Shop Name, Address, Phone Number</strong> update करो — दरेक receipt पर देखाशे.",
        "<strong>Shop Logo</strong> upload करो — receipt अने sidebar पर देखाशे.",
        "<strong>GST Number</strong> enter करो (जो applicable होय).",
        "<strong>Modules</strong> section मां features enable/disable करो: Sales History, Purchases, Expenses, Customers, Barcodes, Reports, Sale Returns.",
        "<strong>Save Settings</strong> click करीने changes apply करो."
      ],
      tips:[
        "Module disable करवाथी ते feature बधा users माते sidebar मांथी छूपाई जाय.",
        "Customer ने receipt आपतां पहेलां shop name अने address जरूर check करो."
      ]
    }
  ]
}
};

let lang = localStorage.getItem('mf_pos_guide_lang') || 'en';

function setLang(l) {
    lang = l;
    localStorage.setItem('mf_pos_guide_lang', l);
    render();
    document.querySelectorAll('.guide-lang-btn').forEach(b => b.classList.toggle('active', b.getAttribute('data-l') === l));
}

function toggleSec(id) {
    document.getElementById('gs-' + id).classList.toggle('collapsed');
}

function render() {
    const d = G[lang];
    document.getElementById('g-title').textContent    = d.title;
    document.getElementById('g-subtitle').textContent = d.subtitle;
    document.getElementById('g-intro-title').textContent = d.intro_title;
    document.getElementById('g-intro-text').textContent  = d.intro_text;
    document.getElementById('g-nav-label').textContent   = d.nav_label;

    document.getElementById('g-quicknav').innerHTML = d.sections.map(s => `
        <div class="col-6 col-md-3">
            <a class="qn-card" href="#gs-${s.id}"
               onclick="document.getElementById('gs-${s.id}').classList.remove('collapsed');setTimeout(()=>document.getElementById('gs-${s.id}').scrollIntoView({behavior:'smooth',block:'start'}),60)">
                <div class="qn-icon" style="background:${s.bg}"><i class="bi ${s.icon}"></i></div>
                <span class="qn-label">${s.nav}</span>
            </a>
        </div>`).join('');

    document.getElementById('g-sections').innerHTML = d.sections.map(s => `
        <div class="g-card" id="gs-${s.id}">
            <div class="g-card-header" onclick="toggleSec('${s.id}')">
                <div class="sec-icon" style="background:${s.bg}"><i class="bi ${s.icon}"></i></div>
                <div style="flex:1;min-width:0">
                    <div class="sec-title">${s.title}</div>
                    <div class="sec-desc">${s.desc}</div>
                </div>
                <i class="bi bi-chevron-down chev"></i>
            </div>
            <div class="g-card-body">
                <ol class="guide-steps">${s.steps.map(st => `<li>${st}</li>`).join('')}</ol>
                ${s.tips && s.tips.length ? `<div class="tip-box"><div class="tip-hdr"><i class="bi bi-lightbulb-fill me-1"></i>Tips</div><ul>${s.tips.map(t=>`<li>${t}</li>`).join('')}</ul></div>` : ''}
            </div>
        </div>`).join('');
}

render();
document.querySelectorAll('.guide-lang-btn').forEach(b => b.classList.toggle('active', b.getAttribute('data-l') === lang));
</script>
@endpush
