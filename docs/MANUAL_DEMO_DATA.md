# Manual demo data

Enter records in this order. This guide contains sample data only; it does not seed the database.

Use `2026-10-08` as the working date and keep every record active.

## 1. System settings

### Lead sources

| Name |
| --- |
| Facebook |
| Website |
| Referral |
| Phone call |
| Walk-in |
| Campaign |

### Lead pipeline stages

| Name | Code |
| --- | --- |
| Lead In | C0 |
| Qualified | C1 |
| Proposal | C2 |
| Negotiation | C3 |
| Closed | C4 |

### Departments

| Name | Code |
| --- | --- |
| Sales | DPT-SAL |
| Marketing | DPT-MKT |
| Service | DPT-SVC |
| Finance | DPT-FIN |
| Operations | DPT-OPS |

## 2. CRM lookup values

Add these where the corresponding setup screen is available. They are required for complete lead and campaign data.

| Lookup | Values |
| --- | --- |
| Segments | Corporate, Retail, Government, SME |
| Lead status | Cold, Warm, Hot, Won, Lost |
| Vehicle colors | White, Black, Red, Blue, Silver |
| To-do types | Follow-up Call, Meeting, Email, Visit, Quotation, Documentation |
| Event types | Vehicle Launch, Test Drive, Roadshow, Showroom Event, Digital Campaign |

## 3. Activity setup

### Activity types and subtypes

| Type | Subtypes |
| --- | --- |
| Phone | Incoming call; Outgoing call; Follow-up call |
| Meeting | In person; Online meeting; Test drive |
| Email | Sent email; Received email; Quotation email |
| Facebook | Page message; Comment; Lead form |
| WhatsApp | New inquiry; Follow-up; Document share |
| Desk work | Quotation; Documentation; Research |

### Activity Type For

| Display name | Module |
| --- | --- |
| Customer | Customer |
| Lead | Lead |
| Contact | Contact |
| Organization | Organization |

## 4. Store, positions, employees and catalogue

### Showroom / warehouse

| Store | Code | Phone | Address |
| --- | --- | --- | --- |
| Main Showroom | DHK-01 | 01711000001 | Tejgaon, Dhaka |
| Chattogram Showroom | CTG-01 | 01711000002 | Agrabad, Chattogram |
| Central Warehouse | WH-01 | 01711000003 | Tongi, Gazipur |

| Store | Position | Code |
| --- | --- | --- |
| Main Showroom | Display Floor | DHK-DISPLAY |
| Main Showroom | Delivery Bay | DHK-DELIVERY |
| Chattogram Showroom | Display Floor | CTG-DISPLAY |
| Central Warehouse | Vehicle Yard | WH-YARD |
| Central Warehouse | Parts Rack A | WH-PARTA |

### Employees

Create the required roles first if they are not available.

| Name | Employee code | Email | Phone | Department | Position | Store | Designation | Salary |
| --- | --- | --- | --- | --- | --- | --- | --- | ---: |
| Tanvir Hasan | EMP-1001 | tanvir@example.test | 01711030001 | Sales | Display Floor | Main Showroom | Sales Executive | 35000 |
| Rupa Sultana | EMP-1002 | rupa@example.test | 01711030002 | Marketing | Display Floor | Main Showroom | Marketing Executive | 40000 |
| Imran Kabir | EMP-1003 | imran@example.test | 01711030003 | Operations | Vehicle Yard | Central Warehouse | Inventory Officer | 38000 |
| Nabila Rahman | EMP-1004 | nabila@example.test | 01711030004 | Finance | Delivery Bay | Main Showroom | Accounts Officer | 42000 |

### Item categories

SUV, Sedan, Pickup, Motorcycle, Spare Parts

### Suppliers

| Supplier | Phone | Email | Address |
| --- | --- | --- | --- |
| Auto Imports Bangladesh Ltd. | 01711001001 | sales@autoimports.test | Tejgaon, Dhaka |
| Prime Motors Trading | 01711001002 | info@primemotors.test | Uttara, Dhaka |
| Eastern Vehicle Solutions | 01711001003 | sales@easternvehicle.test | Agrabad, Chattogram |

### Products

| Product | Part no. | Category | Unit | Unit price | Reorder level |
| --- | --- | --- | --- | ---: | ---: |
| Toyota Corolla Cross Hybrid 2026 | TCC-HV-2026 | SUV | Unit | 4800000 | 1 |
| Honda Vezel e:HEV 2026 | HVZ-EHV-2026 | SUV | Unit | 4500000 | 1 |
| Toyota Axio Hybrid 2025 | TAX-HY-2025 | Sedan | Unit | 3250000 | 1 |
| Mitsubishi L200 Double Cab | ML200-DC-2026 | Pickup | Unit | 5200000 | 1 |
| Yamaha FZ-S V3 | YFZS-V3-2026 | Motorcycle | Unit | 310000 | 2 |
| Engine Oil 5W-30 (4L) | EO-5W30-4L | Spare Parts | Can | 6800 | 10 |

### Customers

| Customer | Type | Phone | Email | Address |
| --- | --- | --- | --- |
| Rahim Ahmed | Retail | 01711010001 | rahim@example.test | Dhanmondi, Dhaka |
| Nusrat Jahan | Retail | 01711010002 | nusrat@example.test | Banani, Dhaka |
| Greenfield Logistics Ltd. | Wholesale | 01711010003 | accounts@greenfield.test | Gulshan, Dhaka |
| Delta Transport Ltd. | Wholesale | 01711010004 | fleet@delta.example.test | Mirpur, Dhaka |

## 5. CRM organizations, contacts and leads

### Organizations

| Organization | Type | Contact person | Phone | Email | Address |
| --- | --- | --- | --- | --- | --- |
| Greenfield Logistics Ltd. | Corporate | Mahmud Karim | 01711010003 | accounts@greenfield.test | Gulshan, Dhaka |
| Delta Transport Ltd. | Corporate | Shafiq Islam | 01711010004 | fleet@delta.example.test | Mirpur, Dhaka |

### Contacts

| Name | Organization | Mobile | Email | Designation |
| --- | --- | --- | --- | --- |
| Mahmud Karim | Greenfield Logistics Ltd. | 01711020003 | mahmud@example.test | Director |
| Shafiq Islam | Delta Transport Ltd. | 01711020005 | shafiq@example.test | Fleet Procurement Manager |

### Leads

Assign every lead to System Administrator. Use the matching lookup values created above.

| Lead | Phone | Segment | Product | Color | Status | Pipeline | Activity type | Source | Detail |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Arif Hossain | 01711020001 | Retail | Toyota Corolla Cross Hybrid 2026 | White | Hot | Qualified | Phone | Facebook | Lead form enquiry |
| Sabila Rahman | 01711020002 | Retail | Honda Vezel e:HEV 2026 | Black | Warm | Lead In | WhatsApp | Website | Website enquiry |
| Mahmud Karim | 01711020003 | Corporate | Mitsubishi L200 Double Cab | Silver | Hot | Proposal | Meeting | Referral | Existing customer referral |
| Farzana Islam | 01711020004 | Retail | Toyota Axio Hybrid 2025 | Red | Cold | Lead In | Facebook | Facebook | Page message |
| Shafiq Islam | 01711020005 | Corporate | Mitsubishi L200 Double Cab | White | Warm | Negotiation | Email | Campaign | Fleet campaign |

Add these activities after creating the leads.

| Subject lead | Type / subtype | Activity with | Priority | Remarks | Follow-up |
| --- | --- | --- | --- | --- | --- |
| Arif Hossain | Phone / Outgoing call | Arif Hossain | High | Discussed specifications and financing. | Follow-up Call, 2026-10-09 11:00 |
| Sabila Rahman | WhatsApp / New inquiry | Sabila Rahman | Medium | Sent photos, price and colours. | Visit, 2026-10-10 15:00 |
| Mahmud Karim | Meeting / In person | Mahmud Karim | High | Presented fleet proposal. | Quotation, 2026-10-09 14:00 |
| Farzana Islam | Facebook / Page message | Farzana Islam | Low | Shared price and delivery information. | Follow-up Call, 2026-10-10 17:00 |

## 6. Purchases and inventory

Create this purchase from **Auto Imports Bangladesh Ltd.**. Use `piece` as the purchase unit, discount `0`, and paid `0`.

| Product | Quantity | Purchase price | Single sale price | Batch / chassis reference |
| --- | ---: | ---: | ---: | --- |
| Toyota Corolla Cross Hybrid 2026 | 2 | 4350000 | 4800000 | TCC-2026-001 |
| Honda Vezel e:HEV 2026 | 1 | 4050000 | 4500000 | HVZ-2026-001 |
| Toyota Axio Hybrid 2025 | 2 | 2900000 | 3250000 | TAX-2025-001 |
| Mitsubishi L200 Double Cab | 3 | 4750000 | 5200000 | ML200-2026-001 |
| Yamaha FZ-S V3 | 5 | 270000 | 310000 | YFZS-2026-001 |
| Engine Oil 5W-30 (4L) | 30 | 5200 | 6800 | EO-2026-001 |

For Goods Receive, select Central Warehouse / Vehicle Yard and add: `Mitsubishi L200 Double Cab`, quantity `1`, rate `4750000`, payment mode `credit`, purchase type `import`.

For a requisition, request `Engine Oil 5W-30 (4L)`, quantity `6`, remarks `Showroom service stock replenishment`.

For an inventory issue, issue `Engine Oil 5W-30 (4L)`, quantity `2`, from Central Warehouse to Main Showroom, purpose `Customer delivery service package`.

## 7. Sales, dealers and collection

### Dealer

| Dealer | Phone | Email | Address |
| --- | --- | --- | --- |
| Metro Auto Gallery | 01711040001 | sales@metroauto.test | Bashundhara, Dhaka |

### Customer vehicle sale

Create a sale for **Rahim Ahmed**:

| Field | Value |
| --- | --- |
| Product | Toyota Corolla Cross Hybrid 2026 |
| Segment | Retail |
| Sale date | 2026-10-08 |
| Sale price | 4800000 |
| Payment mode | Bank |
| Booking amount | 1000000 |
| Delivery point | Main Showroom |
| Engine no. | 2ZR-FXE-260001 |
| Chassis no. | JTMBR3FVX0D260001 |
| Color | White |
| Remarks | Delivery planned after bank financing approval. |

Create one dealer sale for **Metro Auto Gallery** with `Honda Vezel e:HEV 2026`, quantity `1`, price `4500000`, commission `75000` and booking amount `500000`.

Create a collection against Rahim's vehicle sale: `750000`, date `2026-10-08`, payment mode `bank`, reference `BRAC-TRX-260008`, remarks `Second installment received`.

Create a received cheque: account name `Rahim Ahmed`, cheque no. `CHQ-260801`, cheque date `2026-10-15`, amount `500000`, status `In Hand`, remarks `Final payment cheque`.

## 8. Promotions and planning

### Promotion event

| Field | Value |
| --- | --- |
| Title | Corolla Cross Test Drive Weekend |
| Type | Test Drive |
| Segment | Retail |
| Supervisor | Rupa Sultana |
| Start / end | 2026-10-10 / 2026-10-11 |
| Status | Upcoming |
| Remarks | Test-drive registration campaign at Main Showroom. |

Create an event budget for that event: `Venue decoration 50000`, `Digital advertising 75000`, `Refreshments 25000`, `Test-drive logistics 40000`.

### Plan, agenda and meeting

| Module | Data |
| --- | --- |
| Pre-event plan | `Corolla Cross Test Drive Preparation`; Retail; 2026-10-09 to 2026-10-11; owner Rupa Sultana; status In Progress |
| Agenda | `Prepare test-drive vehicle and customer registration desk`; Retail; status In Progress; 2026-10-09 to 2026-10-10; responsible Imran Kabir |
| Meeting | `Test Drive Campaign Readiness Meeting`; 2026-10-09 16:00; attendees Rupa Sultana, Imran Kabir and Tanvir Hasan; status Upcoming |
| Sales target | Month October 2026; Tanvir Hasan; target quantity 3; target amount 12000000 |

## 9. Finance and HR

The first purchase and sale automatically create accounting entries. Then add:

| Module | Data |
| --- | --- |
| Attendance | 2026-10-08: Tanvir, Rupa, Imran and Nabila = Present; check-in 09:00; check-out 18:00 |
| Opening balance | Main cash debit 200000; bank debit 1500000; capital credit 1700000 |
| Debit voucher | 2026-10-08; pay `15000` from Main Cash to Marketing Expense; narration `Campaign banner printing` |
| Credit voucher | 2026-10-08; receive `50000` into Main Cash from Other Income; narration `Event sponsorship contribution` |
| Supplier payment | Auto Imports Bangladesh Ltd.; `500000`; cash/bank account; date 2026-10-08; narration `Partial settlement against vehicle purchase` |
| Customer receive | Rahim Ahmed; `750000`; bank account; date 2026-10-08; narration `Vehicle booking installment` |

Reports, ledgers, stock ledger, sales history, performance report and financial statements derive their data from the entries above. Add returns, sales adjustments, issue returns and documents only after their related sale or issue is visible in the relevant selector.
