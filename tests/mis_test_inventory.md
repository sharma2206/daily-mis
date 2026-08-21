# MIS Report Test Case Inventory

| ID      | Type    | Module | Test Case          | Expected Result           | Priority |
| ------- | ------- | ------ | ------------------ | ------------------------- | -------- |
| MIS-001 | Unit    | MIS    | FTD & MTD Sales Calculation | Sales correctly accumulated based on patient_type and service_type. | Critical |
| MIS-002 | Unit    | MIS    | Discount Calculation | 100% and partial discounts calculated correctly by patient_type. | Critical |
| MIS-003 | Unit    | MIS    | Refund Calculation | Absolute value of refunds correctly aggregated. | Critical |
| MIS-004 | Unit    | MIS    | MRI Stats Calculation | MRI counts and revenue accurately summed for OP/IP. | High     |
| MIS-005 | Unit    | MIS    | Collections Calculation | Collections grouped correctly by patient_type and Pharmacy. | Critical |
| MIS-006 | Unit    | MIS    | Volume Payload Building | MTD accumulates previously cached volumes correctly, plus current FTD. | High     |
| MIS-007 | Unit    | MIS    | Chromepet Branch Adjustments | Package consumption modifies IP and PH sales exactly. | Critical |
| MIS-008 | Unit    | MIS    | Oragadam Branch Isolation | Package adjustments are strictly isolated from Oragadam. | Critical |
| MIS-009 | Feature | MIS    | Dashboard Endpoint Loads | Returns HTTP 200 with summary block for both branches. | High     |
| MIS-010 | Feature | MIS    | Show Endpoint Loads Valid Data | Returns HTTP 200 with structured JSON MIS payload for branch/date. | High     |
| MIS-011 | Feature | MIS    | Route Constraints Invalid Branch | Returns HTTP 404 for invalid branch string. | Medium   |
| MIS-012 | Feature | MIS    | Route Constraints Invalid Date | Returns HTTP 404 for malformed date string. | Medium   |
| MIS-013 | Feature | MIS    | Upload Endpoint Processes CSVs | Mocked CSV upload succeeds, saves volume payload, returns HTTP 200. | Critical |
| MIS-014 | Feature | MIS    | Export Excel | Triggering export endpoint downloads an Excel file safely. | Medium   |
