import { defineConfig } from "cypress";

export default defineConfig({
  allowCypressEnv: false,
  projectId: "b52qr4",

  e2e: {
    baseUrl: 'http://localhost:8000',

    // setupNodeEvents(on, config) {
    //   // implement node event listeners here
    // },
  },
  env: {
    apiKey: "6274cc57-c612-4f0c-bc0a-1b4da73e0504"
  }
});
