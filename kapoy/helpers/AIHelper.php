<?php
/**
 * AIHelper — General-purpose chatbot for GeoSurvey Portal
 * Handles: greetings, FAQs, platform help, general knowledge,
 *          small talk, surveying topics, and more.
 */
class AIHelper {

    /* ── Keyword → response map ─────────────────────────────── */
    private static $rules = [

        /* Greetings */
        'greeting' => [
            'keywords' => ['hello','hi','hey','good morning','good afternoon','good evening','good day','howdy','greetings','sup','what\'s up','wassup','yo'],
            'responses' => [
                "Hello! 👋 Welcome to GeoSurvey Portal. I'm GeoBot — your AI assistant. How can I help you today?",
                "Hi there! 😊 I'm GeoBot. Ask me anything about land surveying, appointments, payments, or just chat!",
                "Hey! Great to see you. I'm here to help with all your land surveying needs. What can I do for you?",
                "Good day! I'm GeoBot, your 24/7 AI assistant. Feel free to ask me anything!",
            ]
        ],

        /* How are you / bot identity */
        'how_are_you' => [
            'keywords' => ['how are you','how r u','how do you do','are you okay','you good','how\'s it going','how are things'],
            'responses' => [
                "I'm doing great, thanks for asking! 😄 I'm always ready to help. What can I do for you?",
                "Fantastic! I'm an AI so I don't get tired — always here for you 24/7. What do you need?",
                "All systems running perfectly! 🤖 How about you? What can I help you with today?",
            ]
        ],

        /* Bot identity */
        'who_are_you' => [
            'keywords' => ['who are you','what are you','your name','are you a bot','are you ai','are you human','what is geobot'],
            'responses' => [
                "I'm GeoBot 🤖 — the AI assistant for GeoSurvey Portal. I can help you with appointments, services, payments, engineer info, and general questions. Ask me anything!",
                "I'm GeoBot, an AI chatbot built for GeoSurvey Portal. I'm here to answer your questions about land surveying services and help you navigate the platform.",
            ]
        ],

        /* Thank you */
        'thanks' => [
            'keywords' => ['thank you','thanks','thank u','ty','thx','appreciate','salamat','maraming salamat'],
            'responses' => [
                "You're welcome! 😊 Is there anything else I can help you with?",
                "Happy to help! Let me know if you have more questions.",
                "Anytime! That's what I'm here for. 🙌",
                "Glad I could help! Feel free to ask anything else.",
            ]
        ],

        /* Goodbye */
        'goodbye' => [
            'keywords' => ['bye','goodbye','see you','take care','later','cya','good night','goodnight','paalam'],
            'responses' => [
                "Goodbye! 👋 Have a great day. Come back anytime you need help!",
                "See you later! Take care. 😊",
                "Bye! Don't hesitate to return if you have more questions. Have a wonderful day!",
            ]
        ],

        /* ── PLATFORM FEATURES ─────────────────────────────── */

        /* Booking */
        'booking' => [
            'keywords' => ['book','appointment','schedule','reserve','booking','set appointment','make appointment'],
            'responses' => [
                "To book an appointment:\n1. Go to **Book Appointment** in the sidebar\n2. Select a licensed engineer\n3. Choose your service type\n4. Pick an available date (shown in 🟢 green)\n5. Select a time slot and confirm\n\nYou'll receive a confirmation email with your booking code!",
                "Booking is easy! Click **Book Appointment** in the sidebar, choose your engineer and service, then pick a green date on the calendar. Green = available, Red = unavailable.",
                "You can book a survey appointment directly from your dashboard. Head to **Book Appointment**, select your preferred engineer, service, and schedule. Our AI will suggest the best time slots for you!",
            ]
        ],

        /* Services */
        'services' => [
            'keywords' => ['service','survey type','what survey','boundary','topographic','construction layout','subdivision','geodetic','hydrographic','as-built','route survey'],
            'responses' => [
                "We offer 8 professional surveying services:\n\n📍 **Boundary Survey** — ₱5,000+ (3–5 days)\n🏔️ **Topographic Survey** — ₱8,000+ (5–7 days)\n🏗️ **Construction Layout** — ₱6,000+ (2–3 days)\n🏘️ **Subdivision Survey** — ₱15,000+ (7 days)\n🛣️ **Route Survey** — ₱12,000+ (10 days)\n🌊 **Hydrographic Survey** — ₱20,000+ (14 days)\n🌐 **Geodetic Survey** — ₱25,000+ (21 days)\n📐 **As-Built Survey** — ₱7,000+ (4 days)\n\nWhich service are you interested in?",
                "Our most popular services are Boundary Survey and Topographic Survey. All surveys are conducted by PRC-licensed geodetic engineers. Would you like details on a specific service?",
            ]
        ],

        /* Pricing */
        'pricing' => [
            'keywords' => ['price','cost','fee','rate','how much','pricing','charge','magkano','bayad','expensive','cheap','affordable'],
            'responses' => [
                "Our service pricing:\n\n• Boundary Survey — from ₱5,000\n• Topographic Survey — from ₱8,000\n• Construction Layout — from ₱6,000\n• Subdivision Survey — from ₱15,000\n• Route Survey — from ₱12,000\n• Hydrographic Survey — from ₱20,000\n• Geodetic Survey — from ₱25,000\n• As-Built Survey — from ₱7,000\n\nFinal pricing depends on project scope and location. Book an appointment for a detailed quote!",
                "Prices start at ₱5,000 for a Boundary Survey and go up to ₱25,000+ for Geodetic Surveys. The exact cost depends on the area size and complexity. Your assigned engineer will provide a full quote.",
            ]
        ],

        /* Payment */
        'payment' => [
            'keywords' => ['payment','pay','gcash','bank transfer','credit card','paypal','cash','receipt','proof','bayad','how to pay'],
            'responses' => [
                "We accept multiple payment methods:\n\n💙 **GCash** — 0917-123-4567\n🏦 **Bank Transfer** — BDO Account 1234-5678-9012\n💳 **Credit Card** — via secure gateway\n💵 **Cash** — pay the engineer on survey day\n\nAfter paying, go to **Payments** in your dashboard and upload your proof of payment for verification.",
                "To submit payment:\n1. Go to **Payments** in your sidebar\n2. Select your appointment\n3. Choose your payment method\n4. Upload your receipt/screenshot\n5. Wait for admin verification (within 24 hours)\n\nYou'll be notified once your payment is verified!",
            ]
        ],

        /* Engineers */
        'engineers' => [
            'keywords' => ['engineer','surveyor','licensed','prc','geodetic','find engineer','browse engineer','who are the engineers'],
            'responses' => [
                "All our engineers are **PRC-licensed Geodetic Engineers** with verified credentials. You can browse them in the **Browse Engineers** section — each profile shows:\n\n⭐ Ratings & reviews\n📋 Specialization\n💼 Years of experience\n🏢 Company affiliation\n✅ Availability status\n\nYou can book directly from their profile!",
                "We have 50+ licensed engineers across the Philippines. Go to **Browse Engineers** to filter by availability, specialization, or company. Click any engineer to view their full profile and available slots.",
            ]
        ],

        /* Track status */
        'status' => [
            'keywords' => ['status','track','progress','update','where is','when will','survey done','finished','complete','ongoing'],
            'responses' => [
                "To track your survey:\n1. Go to **Track Status** in your sidebar\n2. Select your appointment\n3. View the real-time progress timeline\n\nYour engineer posts updates directly — you'll also receive notifications for each milestone!",
                "You can monitor your survey progress in real-time from the **Track Status** page. Your engineer will post updates like 'Site inspection done', 'Boundary markers placed', etc. You'll get notified for every update!",
            ]
        ],

        /* Companies */
        'companies' => [
            'keywords' => ['company','companies','firm','office','partner','geotech','precisionmap','landmark','terrascan','northstar'],
            'responses' => [
                "We partner with 5 top surveying companies across the Philippines:\n\n🏢 **GeoTech Surveying Inc.** — Makati City\n🏢 **PrecisionMap Solutions** — Quezon City\n🏢 **LandMark Surveyors Co.** — Cebu City\n🏢 **TerraScan Philippines** — Davao City\n🏢 **NorthStar Survey Group** — Iloilo City\n\nGo to **Companies** in the sidebar to view their profiles, services, and Google Maps location!",
                "Browse our partner companies in the **Companies** section. Each company profile shows their services, engineers, contact info, and an interactive Google Map of their office location.",
            ]
        ],

        /* Notifications */
        'notifications' => [
            'keywords' => ['notification','notify','alert','bell','reminder','email notification'],
            'responses' => [
                "You'll receive notifications for:\n\n📅 Appointment confirmations & updates\n💳 Payment verification results\n💬 New messages from engineers\n📋 Survey progress updates\n\nNotifications appear in the bell icon (top right) and as pop-up toasts. You can also manage email notification preferences in **Settings**.",
            ]
        ],

        /* Messages / Chat */
        'messages' => [
            'keywords' => ['message','chat','contact engineer','send message','inbox','talk to engineer','communicate'],
            'responses' => [
                "You can message engineers directly through the **Messages** section! Features include:\n\n💬 Real-time chat\n📎 File & image attachments\n🎤 Voice messages\n😊 Emoji support\n\nJust go to **Messages** in the sidebar and start a conversation with any engineer.",
                "The Messages feature works like WhatsApp! You can send text, images, files, and even voice messages to your engineer. Find it in the **Messages** section of your sidebar.",
            ]
        ],

        /* Feedback / Reviews */
        'feedback' => [
            'keywords' => ['feedback','review','rating','rate','stars','comment','testimonial'],
            'responses' => [
                "After your survey is completed, you can leave a review in the **Feedback** section:\n\n⭐ Rate your engineer (1–5 stars)\n💬 Write a detailed comment\n\nYour feedback helps other clients choose the right engineer and helps engineers improve their service!",
            ]
        ],

        /* Registration / Login */
        'register' => [
            'keywords' => ['register','sign up','create account','join','new account','how to register'],
            'responses' => [
                "Creating an account is free and easy!\n\n1. Click **Get Started** on the homepage\n2. Choose your role: **Client** or **Engineer**\n3. Fill in your details\n4. Upload a profile photo (optional)\n5. Click **Create Account**\n\nYou'll be logged in immediately!",
            ]
        ],

        'login' => [
            'keywords' => ['login','log in','sign in','forgot password','can\'t login','password reset'],
            'responses' => [
                "To log in, go to the **Sign In** page and enter your email and password.\n\nDemo accounts:\n• Client: client@test.com / 123456\n• Engineer: engineer@test.com / 123456\n• Admin: admin@test.com / 123456\n\nForgot your password? Go to **Settings → Password** to change it after logging in.",
            ]
        ],

        /* Dark mode */
        'dark_mode' => [
            'keywords' => ['dark mode','light mode','theme','dark theme','night mode'],
            'responses' => [
                "You can toggle **Dark Mode** anytime! Click the 🌙 moon icon in the top-right header. Your preference is saved automatically — it'll stay dark even after you refresh the page.",
            ]
        ],

        /* Language */
        'language' => [
            'keywords' => ['language','translate','filipino','tagalog','spanish','chinese','japanese','korean','arabic','lang'],
            'responses' => [
                "You can change the interface language using the 🌐 language selector in the top-right header. Available languages:\n\n🇵🇭 English / Filipino\n🇪🇸 Español\n🇨🇳 中文\n🇯🇵 日本語\n🇰🇷 한국어\n🇸🇦 العربية",
            ]
        ],

        /* ── GENERAL KNOWLEDGE ─────────────────────────────── */

        /* What is land surveying */
        'what_is_survey' => [
            'keywords' => ['what is land survey','what is surveying','define survey','meaning of survey','what does surveyor do'],
            'responses' => [
                "**Land surveying** is the science of measuring and mapping the Earth's surface. Surveyors determine:\n\n📍 Property boundaries\n📐 Elevations and contours\n🏗️ Construction positions\n🗺️ Geographic coordinates\n\nIn the Philippines, land surveys are required for property titles, construction permits, and subdivision approvals.",
                "Land surveying involves precise measurement of land areas, boundaries, and features. A licensed **Geodetic Engineer** uses GPS, total stations, and drones to create accurate maps and legal documents for property transactions.",
            ]
        ],

        /* PRC license */
        'prc' => [
            'keywords' => ['prc','license','licensed','geodetic engineer','board exam','prc license number'],
            'responses' => [
                "In the Philippines, land surveyors must be **PRC-licensed Geodetic Engineers**. The PRC (Professional Regulation Commission) issues licenses after passing the Geodetic Engineering board exam. All engineers on our platform have verified PRC licenses.",
            ]
        ],

        /* Title transfer */
        'title' => [
            'keywords' => ['title','land title','transfer title','lot title','property title','tcl','ocl','deed of sale'],
            'responses' => [
                "For **land title transfer** in the Philippines, you typically need:\n\n1. ✅ Boundary Survey (to verify lot boundaries)\n2. 📋 Survey plan approved by DENR/LRA\n3. 📄 Deed of Sale\n4. 🏛️ BIR clearance\n5. 📝 Register of Deeds processing\n\nOur engineers can handle the survey requirements. Book a **Boundary Survey** to get started!",
            ]
        ],

        /* DENR / LRA */
        'government' => [
            'keywords' => ['denr','lra','hlurb','namria','register of deeds','land registration','government','approval'],
            'responses' => [
                "Key government agencies for land surveys in the Philippines:\n\n🏛️ **DENR** — approves survey plans\n🏛️ **LRA** — Land Registration Authority\n🏛️ **NAMRIA** — National Mapping & Resource Info Authority\n🏛️ **HLURB** — subdivision approvals\n\nOur engineers are accredited with these agencies and can handle all documentation requirements.",
            ]
        ],

        /* GPS / Technology */
        'technology' => [
            'keywords' => ['gps','drone','lidar','total station','autocad','technology','equipment','instrument'],
            'responses' => [
                "Our engineers use modern surveying technology:\n\n🛰️ **GPS/GNSS** — centimeter-level accuracy\n🚁 **Drone/UAV** — aerial mapping & photogrammetry\n📡 **LiDAR** — 3D point cloud scanning\n📏 **Total Station** — precise angle & distance measurement\n💻 **AutoCAD Civil 3D** — professional plan drafting\n\nThis ensures fast, accurate, and reliable survey results!",
            ]
        ],

        /* Weather */
        'weather' => [
            'keywords' => ['weather','rain','typhoon','storm','sunny','climate','forecast'],
            'responses' => [
                "Weather can affect survey schedules! Heavy rain, typhoons, or poor visibility may cause rescheduling. Your engineer will notify you if weather conditions require a date change. We recommend booking morning slots during dry season (November–May) for best results.",
            ]
        ],

        /* Philippines geography */
        'philippines' => [
            'keywords' => ['philippines','pilipinas','manila','cebu','davao','luzon','visayas','mindanao','metro manila'],
            'responses' => [
                "GeoSurvey Portal serves clients across the Philippines! We have engineers in:\n\n🏙️ Metro Manila (Makati, BGC, Quezon City)\n🌴 Cebu City\n🌺 Davao City\n🏝️ Iloilo City\n\nAnd expanding to more areas. Check the **Companies** section to find engineers near you!",
            ]
        ],

        /* Time / Duration */
        'duration' => [
            'keywords' => ['how long','duration','days','weeks','time','finish','complete','turnaround'],
            'responses' => [
                "Survey completion times:\n\n• Boundary Survey — 3–5 days\n• Topographic Survey — 5–7 days\n• Construction Layout — 2–3 days\n• Subdivision Survey — 7–10 days\n• Route Survey — 10–14 days\n• Hydrographic Survey — 14 days\n• Geodetic Survey — 21 days\n• As-Built Survey — 4–6 days\n\nTimelines depend on project size and complexity. Your engineer will confirm the exact schedule.",
            ]
        ],

        /* Documents */
        'documents' => [
            'keywords' => ['document','report','plan','output','result','deliverable','what will i get','certificate'],
            'responses' => [
                "After your survey, you'll receive:\n\n📋 **Survey Plan** — official DENR-approved plan\n📐 **Technical Description** — legal lot description\n📊 **Survey Report** — detailed findings\n💾 **Digital Files** — AutoCAD/PDF formats\n📸 **Site Photos** — documentation photos\n\nAll documents are signed and sealed by your licensed engineer.",
            ]
        ],

        /* Support / Contact */
        'support' => [
            'keywords' => ['support','help','contact','reach','call','email','phone','office','customer service','hotline'],
            'responses' => [
                "Need help? Here's how to reach us:\n\n📞 **Phone:** +63 2 8123 4567\n📧 **Email:** info@geosurveyportal.ph\n💬 **Messages:** Use the Messages feature in your dashboard\n🕐 **Hours:** Monday–Saturday, 8:00 AM – 5:00 PM\n\nOr just keep chatting with me — I'm available 24/7! 😊",
            ]
        ],

        /* Compliment */
        'compliment' => [
            'keywords' => ['great','awesome','amazing','excellent','good job','well done','nice','perfect','love it','fantastic'],
            'responses' => [
                "Thank you so much! 😊 We work hard to make GeoSurvey Portal the best it can be. Is there anything else I can help you with?",
                "That means a lot! 🙏 We're always improving. Let me know if you need anything else!",
            ]
        ],

        /* Complaint */
        'complaint' => [
            'keywords' => ['problem','issue','complaint','not working','error','bug','wrong','bad','terrible','disappointed','frustrated'],
            'responses' => [
                "I'm sorry to hear you're having trouble! 😔 Here's what you can do:\n\n1. **Refresh the page** and try again\n2. **Clear your browser cache**\n3. **Contact support** via Messages or email info@geosurveyportal.ph\n4. **Call us** at +63 2 8123 4567\n\nWe'll resolve your issue as quickly as possible!",
            ]
        ],

        /* Jokes / Fun */
        'joke' => [
            'keywords' => ['joke','funny','laugh','humor','tell me a joke','make me laugh'],
            'responses' => [
                "Why did the surveyor break up with the GPS? Because it kept saying 'recalculating'! 😄\n\nNeed help with something else?",
                "Why do surveyors make great friends? Because they always know where you stand! 📍😂",
                "What did the boundary marker say to the property owner? 'I've got you covered!' 😄",
            ]
        ],

        /* Math / Calculations */
        'math' => [
            'keywords' => ['calculate','area','hectare','square meter','sqm','lot size','compute','measurement'],
            'responses' => [
                "For land area calculations:\n\n• 1 hectare = 10,000 sq meters\n• 1 sq meter = 0.0001 hectares\n• 1 acre = 4,047 sq meters\n\nFor precise lot area computation, our engineers use GPS and total station measurements. Book a survey for an official, legally-recognized measurement!",
            ]
        ],

        /* Default fallback */
        'default' => [
            'keywords' => [],
            'responses' => [
                "I'm not sure I fully understood that, but I'm here to help! 😊 You can ask me about:\n\n📅 Booking appointments\n💰 Service pricing\n👷 Finding engineers\n📍 Tracking your survey\n💳 Payment methods\n🏢 Partner companies\n\nWhat would you like to know?",
                "Hmm, I didn't quite catch that. Could you rephrase? I can help with appointments, services, payments, engineer info, and general surveying questions!",
                "I'm still learning! 🤖 For that specific question, please contact our support team at info@geosurveyportal.ph or use the Messages feature to chat with our team directly.",
                "Great question! For the most accurate answer, I'd recommend reaching out to our support team. In the meantime, is there anything else about our surveying services I can help with?",
            ]
        ],
    ];

    /**
     * Main entry point — generate a response for any user message.
     */
    public static function generateResponse(string $message): string {
        $msg = mb_strtolower(trim($message));

        if (empty($msg)) {
            return "Please type a message and I'll do my best to help! 😊";
        }

        // Try each rule in order
        foreach (self::$rules as $key => $rule) {
            if ($key === 'default') continue;
            if (self::matches($msg, $rule['keywords'])) {
                return self::pick($rule['responses']);
            }
        }

        // Fallback
        return self::pick(self::$rules['default']['responses']);
    }

    /**
     * Suggest appointment slots (used by booking system).
     */
    public static function suggestSlots(string $service_type, array $available_slots): string {
        if (empty($available_slots)) {
            return "I couldn't find available slots for the selected date. Please try a different date — green dates on the calendar have open slots!";
        }

        $suggestions = [];
        foreach (array_slice($available_slots, 0, 3) as $slot) {
            $suggestions[] = date('h:i A', strtotime($slot['start_time']))
                           . ' – '
                           . date('h:i A', strtotime($slot['end_time']));
        }

        $tips = [
            'Boundary Survey'     => 'Morning slots (8AM–12PM) are best for boundary surveys — better light and fewer distractions.',
            'Topographic Survey'  => 'Full-day slots are recommended for topographic surveys to ensure complete coverage.',
            'Construction Layout' => 'Early morning slots avoid site traffic and give the engineer maximum working time.',
            'Geodetic Survey'     => 'Full-day slots are ideal for geodetic surveys requiring extensive measurements.',
            'default'             => 'Morning slots generally offer the best conditions for accurate surveying work.',
        ];

        $tip = $tips[$service_type] ?? $tips['default'];
        return $tip . "\n\nAvailable slots:\n• " . implode("\n• ", $suggestions);
    }

    /* ── Private helpers ─────────────────────────────────────── */

    private static function matches(string $msg, array $keywords): bool {
        foreach ($keywords as $kw) {
            if (mb_strpos($msg, $kw) !== false) return true;
        }
        return false;
    }

    private static function pick(array $responses): string {
        return $responses[array_rand($responses)];
    }
}