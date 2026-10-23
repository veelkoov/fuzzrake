import { defineConfig } from "vite";
import Symfony from "@symfony/reprise/vite";

export default defineConfig({
    input: {
        creator_card: "./assets/scripts/entry/creator_card.ts",
        events: "./assets/scripts/entry/events.ts",
        feedback: "./assets/scripts/entry/feedback.ts",
        general: "./assets/scripts/entry/general.ts",
        iu_form: "./assets/scripts/entry/iu_form.ts",
        main: "./assets/scripts/entry/main.ts",
        submissions: "./assets/scripts/entry/submissions.ts",
        toc: "./assets/scripts/entry/toc.ts",
        user: "./assets/scripts/entry/user.ts",
        user_password: "./assets/scripts/entry/user_password.ts",
    },
    plugins: [
        Symfony({
            // options
        }),
    ],
});
