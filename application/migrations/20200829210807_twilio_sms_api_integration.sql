-- twilio_sms_api_integration --
ALTER TABLE phppos_locations ADD COLUMN twilio_sid VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE phppos_locations ADD COLUMN twilio_token VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE phppos_locations ADD COLUMN twilio_sms_from VARCHAR(255) NULL DEFAULT NULL;