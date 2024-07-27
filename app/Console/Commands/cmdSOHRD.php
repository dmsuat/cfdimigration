<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB; //For database transactions
use Carbon\Carbon;
use App\Models\LoadedSO;
use App\Models\ARTRAN;
use App\Models\SOHDR;
use App\Models\IVTRAN;
use App\Models\SOLIN;
use App\Models\SOHDRH;
use App\Models\SOLINH;
use App\Models\SODRHDR;
use App\Models\SODRLIN;
use App\Models\SORETURNHDR;
use App\Models\UnappliedAmount;
use App\Models\AllocationAssignment;
use App\Models\IVHDRH;
use App\Models\IVLINH;
use App\Models\SORETURNLIN;
use App\Models\ReceiptsPrincipalHDR;
use App\Models\ReceiptsPrincipalLIN;
use App\Models\SOCreditCheck;


use SplFileObject;

class cmdSOHRD extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cmd-s-o-h-r-d';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    // Helper function to format dates or return NULL
    private function formatDate($dateString) {
        if (empty($dateString)) {
            return 'NULL'; 
        }
    
        $timestamp = strtotime($dateString);
        if ($timestamp !== false) {
            $formattedDate = date('Y-m-d H:i:s', $timestamp);
            return "CONVERT(datetime, '$formattedDate', 120)"; // Add CONVERT
        } else {
            return 'NULL';
        }
    }
    public function handle()
    {
        $filepath = $this->ask('Enter the path to the CSV file:');
        $casepath = $this->ask('Enter the file you want to import');

        switch($casepath) {

            case "LOADEDSO":
                if (!file_exists($filepath)) {
                    $this->error("File not found: $filepath");
                    return;
                }

                $file = new SplFileObject($filepath, 'r');
                $file->setFlags(SplFileObject::READ_CSV);
                $file->setCsvControl('|'); // Pipe delimiter

                // Skip header row
                $file->current(); 
                $file->next();
        
                try {
                    // Wrap the operation in a database transaction
                    $rowCount = 0;
                    foreach($file as $row) {
                        if ($rowCount > 0) {
                            if ($row && !empty($row[0])) {

                                $insertValues = [
                                    "'{$row[0]}'",         // Distributor
                                    "'{$row[1]}'",         // Salesman
                                    "'{$row[2]}'",         // Docno
                                    "'{$row[3]}'",        // Location
                                    $this->formatDate($row[4]),  // OrderDate (use formatDate)
                                    $this->formatDate($row[5]),  // ReqDelDate (use formatDate)
                                    "'{$row[6]}'",         // PaymentTerm
                                    "'{$row[7]}'",         // AccountCode
                                    "'{$row[8]}'",         // ProductCode
                                    "'{$row[9]}'",         // NumberofOrders
                                    "'{$row[10]}'",        // OrderUOM
                                    $this->formatDate($row[11]), // SystemDate (use formatDate)
                                    "'{$row[12]}'",        // SystemUser
                                    "'{$row[13]}'",        // Rownum
                                    "'{$row[14]}'",        // LoadedBy
                                    $this->formatDate($row[15]), // DateLoaded (use formatDate)
                                    "'{$row[16]}'",        // Status
                                    "'{$row[17]}'",        // SONo
                                    "'{$row[18]}'",        // SendStatus
                                    "'{$row[19]}'"         // Remarks
                                ];
                
                                $insertScript[] = "INSERT INTO LoadedSO (Distributor, Salesman, Docno, Location, OrderDate, ReqDelDate, PaymentTerm, AccountCode, ProductCode, NumberofOrders, OrderUOM, SystemDate, SystemUser, Rownum, LoadedBy, DateLoaded, Status, SONo, SendStatus, Remarks) VALUES (" . implode(',', $insertValues) . ");";
                            }
                        }
                        $rowCount++;
                    }
                    // Write INSERT statements to file
                    $outputFilename = 'loadedso_insert_script.sql';
                    $filePath = base_path('app/Console/Commands/' . $outputFilename);
                    file_put_contents($filePath, implode("\n", $insertScript));

                    $this->info("Insert script successfully generated at: $filePath");

                } catch (\Exception $e) {
                    // DB::rollBack();
                    $this->error("Error inserting rows: {$e->getMessage()}");
                }
                break;
            
            case "ARTRAN":
                if (!file_exists($filepath)) {
                    $this->error("File not found: $filepath");
                    return;
                }

                $file = new SplFileObject($filepath, 'r');
                $file->setFlags(SplFileObject::READ_CSV);
                $file->setCsvControl('|'); // Pipe delimiter

                // Skip header row
                $file->current(); 
                $file->next();
        
                try {
                    
                    $rowCount = 0;
                    $insertScript = [];  // Array to store INSERT statements

                    foreach ($file as $row) {
                        if ($rowCount > 0 && $row && !empty($row[0])) {
                            // Generate INSERT statement
                            $insertValues = [
                                "'{$row[0]}'",        // Docno
                                "'{$row[1]}'",        // Applyto
                                "'{$row[2]}'",        // Doctype
                                "'{$row[3]}'",        // Customercode
                                "'{$row[4]}'",        // Paymentcode
                                $this->formatDate($row[5]), // Docdate (use formatDate)
                                $this->formatDate($row[6]), // Duedate (use formatDate)
                                "'{$row[7]}'",        // Docamt
                                "'{$row[8]}'",        // Recuser
                                $this->formatDate($row[9]), // Recdate (use formatDate)
                                "'{$row[10]}'",       // Dochome
                                "'{$row[11]}'",       // Distributor
                            ];

                            $insertScript[] = "INSERT INTO ARTRAN (Docno, Applyto, Doctype, Customercode, Paymentcode, Docdate, Duedate, Docamt, Recuser, Recdate, Dochome, Distributor) VALUES (" . implode(',', $insertValues) . ");";
                        }
                        $rowCount++;
                    }

                    // Write INSERT statements to file
                    $outputFilename = 'artran_insert_script.sql';
                    $filePath = base_path('app/Console/Commands/' . $outputFilename); // Generate full path
                    file_put_contents($filePath, implode("\n", $insertScript));
                   
                    $this->info("Insert script successfully generated: $outputFilename");
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error inserting rows: {$e->getMessage()}");
                }
                break;
            case "SOHDR":
                    if (!file_exists($filepath)) {
                        $this->error("File not found: $filepath");
                        return;
                    }
    
                    $file = new SplFileObject($filepath, 'r');
                    $file->setFlags(SplFileObject::READ_CSV);
                    $file->setCsvControl('|'); // Pipe delimiter
    
                    // Skip header row
                    $file->current(); 
                    $file->next();
            
                    try {
                        
                        $rowCount = 0;
                        $insertScript = ["BEGIN TRAN;"];  // Start with BEGIN TRAN

                        foreach ($file as $row) {
                            if ($rowCount > 0 && $row && !empty($row[0])) {
                                $insertValues = [
                                    "'{$row[0]}'",        // Docno
                                    $this->formatDate($row[1]),   // Docdate
                                    "'{$row[2]}'",        // Customercode
                                    "'{$row[3]}'",        // Discountcode
                                    "'{$row[4]}'",        // Shipto
                                    "'{$row[5]}'",        // Poref
                                    "'{$row[6]}'",        // Paymentcode
                                    "'{$row[7]}'",        // Dropship
                                    "'{$row[8]}'",        // Procstat
                                    $this->formatDate($row[9]),   // Reqdate
                                    "'{$row[10]}'",       // Totalsales
                                    "'{$row[11]}'",       // Lessdiscount
                                    "'{$row[12]}'",       // Salesdiscount
                                    "'{$row[13]}'",       // Addvat
                                    "'{$row[14]}'",       // Grandtotal
                                    "'{$row[15]}'",       // Spare1
                                    "'{$row[16]}'",       // Spare2
                                    "'{$row[17]}'",       // Spare3
                                    "'{$row[18]}'",       // Recuser
                                    $this->formatDate($row[19]),   // Recdate
                                    "'{$row[20]}'",       // Moduser
                                    $this->formatDate($row[21]),   // Moddate
                                    "'{$row[22]}'",       // Postuser
                                    $this->formatDate($row[23]),   // Postdate
                                    "'{$row[24]}'",       // Printuser
                                    $this->formatDate($row[25]),   // Printdate
                                    "'{$row[26]}'",       // PriceMatrixcode
                                    "'{$row[27]}'",       // Remarks
                                    "'{$row[28]}'",       // Distributor
                                    "'{$row[29]}'",       // Salesman
                                    "'{$row[30]}'",       // isPassed
                                    $this->formatDate($row[31]),   // PODate
                                    "'{$row[32]}'",       // ForbidPartial
                                ];

                                $insertScript[] = "INSERT INTO SPM.SOHDR (Docno, Docdate, Customercode, Discountcode, Shipto, Poref, Paymentcode, Dropship, Procstat, Reqdate, Totalsales, Lessdiscount, Salesdiscount, Addvat, Grandtotal, Spare1, Spare2, Spare3, Recuser, Recdate, Moduser, Moddate, Postuser, Postdate, Printuser, Printdate, PriceMatrixcode, Remarks, Distributor, Salesman, isPassed, PODate, ForbidPartial) VALUES (" . implode(',', $insertValues) . ");";
                            }
                            $rowCount++;
                        }
                        $insertScript[] = "ROLLBACK TRAN;"; // Append ROLLBACK TRAN

                        // Write INSERT statements to file
                        $outputFilename = 'sohdr_insert_script.sql';
                        $filePath = base_path('app/Console/Commands/' . $outputFilename);
                        file_put_contents($filePath, implode("\n", $insertScript));

                        $this->info("Insert script successfully generated at: $filePath");
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error("Error inserting rows: {$e->getMessage()}");
                    }
                    break;
            case "IVTRAN":
                    if (!file_exists($filepath)) {
                        $this->error("File not found: $filepath");
                        return;
                    }
    
                    $file = new SplFileObject($filepath, 'r');
                    $file->setFlags(SplFileObject::READ_CSV);
                    $file->setCsvControl('|'); // Pipe delimiter
    
                    // Skip header row
                    $file->current(); 
                    $file->next();
            
                    try {
                        $rowCount = 0;
                        $insertScript = ["BEGIN TRAN;"];
        
                        foreach ($file as $row) {
                            if ($rowCount > 0 && $row && !empty($row[0])) {
                                // Generate INSERT statement (with direct date formatting)
                                $insertValues = [
                                    "'{$row[0]}'",        // Distributor
                                    "'{$row[1]}'",        // DocType
                                    (isset($row[2]) && !empty($row[2])) ? "CONVERT(datetime, '" . date('Y-m-d H:i:s', strtotime($row[2])) . "', 120)" : 'NULL', // Docdate
                                    "'{$row[3]}'",        // Docno
                                    "'{$row[4]}'",        // ApplyTo
                                    "'{$row[5]}'",        // Location
                                    "'{$row[6]}'",        // Productcode
                                    (isset($row[7]) && !empty($row[7])) ? "CONVERT(datetime, '" . date('Y-m-d H:i:s', strtotime($row[7])) . "', 120)" : 'NULL', // ExpiryDate
                                    "'{$row[8]}'",        // Inventoriable
                                    "'{$row[9]}'",        // Qty
                                    "'{$row[10]}'",       // UnitCost
                                    "'{$row[11]}'",       // StockUom
                                    "'{$row[12]}'",       // TranUom
                                    "'{$row[13]}'",       // Rownum
                                    "'{$row[14]}'",       // Remarks
                                    "'{$row[15]}'",       // Procstat
                                    "'{$row[16]}'",       // PostUser
                                    (isset($row[17]) && !empty($row[17])) ? "CONVERT(datetime, '" . date('Y-m-d H:i:s', strtotime($row[17])) . "', 120)" : 'NULL', // PostDate
                                    "'{$row[18]}'",       // BatchNo
                                ];
        
                                $insertScript[] = "INSERT INTO SPM.IVTRAN (Distributor, DocType, Docdate, Docno, ApplyTo, Location, Productcode, ExpiryDate, Inventoriable, Qty, UnitCost, StockUom, TranUom, Rownum, Remarks, Procstat, PostUser, PostDate, BatchNo) VALUES (" . implode(',', $insertValues) . ");";
                            }
                            $rowCount++;
                        }
        
                        $insertScript[] = "ROLLBACK TRAN;";
        
                        // Write INSERT statements to file
                        $outputFilename = 'ivtran_insert_script.sql';
                        $filePath = base_path('app/Console/Commands/' . $outputFilename);
                        file_put_contents($filePath, implode("\n", $insertScript));
        
                        $this->info("Insert script successfully generated at: $filePath");
        
                    } catch (\Exception $e) {
                        $this->error("Error generating insert script: {$e->getMessage()}");
                    }
                    break;
            case "SOLIN":
                    if (!file_exists($filepath)) {
                        $this->error("File not found: $filepath");
                        return;
                    }
    
                    $file = new SplFileObject($filepath, 'r');
                    $file->setFlags(SplFileObject::READ_CSV);
                    $file->setCsvControl('|'); // Pipe delimiter
    
                    // Skip header row
                    $file->current(); 
                    $file->next();
                    try {
                        $rowCount = 0;
                        $insertScript = ["BEGIN TRAN;"];
                    
                        foreach ($file as $row) {
                            if ($rowCount > 0 && $row && !empty($row[0])) {
                                $insertValues = [
                                    "'{$row[0]}'",        // DocNo
                                    "'{$row[1]}'",        // LineType
                                    "'{$row[2]}'",        // ProductCode
                                    "'". preg_replace(
                                        [
                                            '/[^\x00-\x7F\xA0-\xFF]/u',   // Remove invalid characters
                                            "/\\\\/",                       // Escape backslashes
                                            "/'/",                         // Escape single quotes
                                            '/(?<!\\\\)\/(?=\d)/'           // Escape forward slash followed by a number (negative lookbehind for escaped backslash)
                                        ],
                                        [
                                            '', 
                                            '\\\\', 
                                            "''", 
                                            '\/'
                                        ],
                                        $row[3]
                                    ) . "'",
                                    "'{$row[4]}'",        // Location
                                    "'{$row[5]}'",        // StockUOM
                                    "'{$row[6]}'",        // OrderUOM
                                    "'{$row[7]}'",        // Qty
                                    "'{$row[8]}'",        // UnitCost
                                    "'{$row[9]}'",        // Amount
                                    "'{$row[10]}'",       // Disc1
                                    "'{$row[11]}'",       // Disc2
                                    "'{$row[12]}'",       // Disc3
                                    "'{$row[13]}'",       // Disc4
                                    "'{$row[14]}'",       // Disc5
                                    "'{$row[15]}'",       // Discount
                                    "'{$row[16]}'",       // NetAmount
                                    "'{$row[17]}'",       // RowNum
                                    "'{$row[18]}'",       // Ref1
                                    "'{$row[19]}'",       // Ref2
                                    "'{$row[20]}'",       // Ref3
                                    "'{$row[21]}'",       // RemQty
                                    "'{$row[22]}'",       // CustomerCode
                                    "'{$row[23]}'",       // Distributor
                                    "'{$row[24]}'",       // RATE1
                                    "'{$row[25]}'",       // RATE2
                                    "'{$row[26]}'",       // RATE3
                                    "'{$row[27]}'",       // RATE1BASIS
                                    "'{$row[28]}'",       // RATE2BASIS
                                    "'{$row[29]}'"        // RATE3BASIS
                                ];
                    
                                $insertScript[] = "INSERT INTO SPM.SOLIN (DocNo, LineType, ProductCode, Description, Location, StockUOM, OrderUOM, Qty, UnitCost, Amount, Disc1, Disc2, Disc3, Disc4, Disc5, Discount, NetAmount, RowNum, Ref1, Ref2, Ref3, RemQty, CustomerCode, Distributor, RATE1, RATE2, RATE3, RATE1BASIS, RATE2BASIS, RATE3BASIS) VALUES (" . implode(',', $insertValues) . ");";
                            }
                            $rowCount++;
                        }
                    
                        $insertScript[] = "ROLLBACK TRAN;"; // End the transaction in the script
                    
                        // Write INSERT statements to file
                        $outputFilename = 'solin_insert_script.sql';
                        $filePath = base_path('app/Console/Commands/' . $outputFilename);
                        file_put_contents($filePath, implode("\n", $insertScript));
                    
                        $this->info("Insert script successfully generated at: $filePath");
                    
                    } catch (\Exception $e) {
                        $this->error("Error generating insert script: {$e->getMessage()}");
                    }
                    break;
            case "SOHDRH":
                    if (!file_exists($filepath)) {
                        $this->error("File not found: $filepath");
                        return;
                    }
    
                    $file = new SplFileObject($filepath, 'r');
                    $file->setFlags(SplFileObject::READ_CSV);
                    $file->setCsvControl('|'); // Pipe delimiter
    
                    // Skip header row
                    $file->current(); 
                    $file->next();
            
                    try {
                        $rowCount = 0;
                        $insertScript = ["BEGIN TRAN;"];  // Start the transaction in the script
                    
                        foreach ($file as $row) {
                            if ($rowCount > 0 && $row && !empty($row[0])) {
                                $insertValues = [
                                    "'{$row[0]}'",        // DocNo
                                    (isset($row[1]) && !empty($row[1])) ? "CONVERT(datetime, '" . date('Y-m-d H:i:s', strtotime($row[1])) . "', 120)" : 'NULL', // DocDate
                                    "'{$row[2]}'",        // CustomerCode
                                    "'{$row[3]}'",        // DiscountCode
                                    "'{$row[4]}'",        // ShipTo
                                    "'{$row[5]}'",        // PORef
                                    "'{$row[6]}'",        // PaymentCode
                                    "'{$row[7]}'",        // DropShip
                                    "'{$row[8]}'",        // Procstat
                                    (isset($row[9]) && !empty($row[9])) ? "CONVERT(datetime, '" . date('Y-m-d H:i:s', strtotime($row[9])) . "', 120)" : 'NULL',   // ReqDate
                                    "'{$row[10]}'",       // TotalSales
                                    "'{$row[11]}'",       // LessDiscount
                                    "'{$row[12]}'",       // SalesDiscount
                                    "'{$row[13]}'",       // AddVAT
                                    "'{$row[14]}'",       // GrandTotal
                                    "'{$row[15]}'",       // Spare1
                                    "'{$row[16]}'",       // Spare2
                                    "'{$row[17]}'",       // Spare3
                                    "'{$row[18]}'",       // RecUser
                                    (isset($row[19]) && !empty($row[19])) ? "CONVERT(datetime, '" . date('Y-m-d H:i:s', strtotime($row[19])) . "', 120)" : 'NULL', // RecDate
                                    "'{$row[20]}'",       // ModUser
                                    (isset($row[21]) && !empty($row[21])) ? "CONVERT(datetime, '" . date('Y-m-d H:i:s', strtotime($row[21])) . "', 120)" : 'NULL', // ModDate
                                    "'{$row[22]}'",       // PostUser
                                    (isset($row[23]) && !empty($row[23])) ? "CONVERT(datetime, '" . date('Y-m-d H:i:s', strtotime($row[23])) . "', 120)" : 'NULL', // PostDate
                                    "'{$row[24]}'",       // PrintUser
                                    (isset($row[25]) && !empty($row[25])) ? "CONVERT(datetime, '" . date('Y-m-d H:i:s', strtotime($row[25])) . "', 120)" : 'NULL', // PrintDate
                                    "'{$row[26]}'",       // PriceMatrixCode
                                    "'{$row[27]}'",       // Remarks
                                    "'{$row[28]}'",       // Distributor
                                    "'{$row[29]}'",       // Salesman
                                    "'{$row[30]}'",       // isPassed
                                    (isset($row[31]) && !empty($row[31])) ? "CONVERT(datetime, '" . date('Y-m-d H:i:s', strtotime($row[31])) . "', 120)" : 'NULL', // PODate
                                    "'{$row[32]}'"        // ForbidPartial
                                ];
                    
                                $insertScript[] = "INSERT INTO SPM.SOHDRH (DocNo, DocDate, CustomerCode, DiscountCode, ShipTo, PORef, PaymentCode, DropShip, Procstat, ReqDate, TotalSales, LessDiscount, SalesDiscount, AddVAT, GrandTotal, Spare1, Spare2, Spare3, RecUser, RecDate, ModUser, ModDate, PostUser, PostDate, PrintUser, PrintDate, PriceMatrixCode, Remarks, Distributor, Salesman, isPassed, PODate, ForbidPartial) VALUES (" . implode(',', $insertValues) . ");";
                            }
                            $rowCount++;
                        }
                    
                        $insertScript[] = "ROLLBACK TRAN;"; // End the transaction in the script
                    
                        // Write INSERT statements to file
                        $outputFilename = 'sohdrh_insert_script.sql';
                        $filePath = base_path('app/Console/Commands/' . $outputFilename);
                        file_put_contents($filePath, implode("\n", $insertScript));
                    
                        $this->info("Insert script successfully generated at: $filePath");
                    
                    } catch (\Exception $e) {
                        $this->error("Error generating insert script: {$e->getMessage()}");
                    }
                    break;
            case "SOLINH":
                if (!file_exists($filepath)) {
                    $this->error("File not found: $filepath");
                    return;
                }

                $file = new SplFileObject($filepath, 'r');
                $file->setFlags(SplFileObject::READ_CSV);
                $file->setCsvControl('|'); // Pipe delimiter

                // Skip header row
                $file->current(); 
                $file->next();
        
                try {
                    $rowCount = 0;
                    $insertScript = ["BEGIN TRAN;"];
                
                    foreach ($file as $row) {
                        if ($rowCount > 0 && $row && !empty($row[0])) {
                            $insertValues = [
                                "'{$row[0]}'",        // DocNo
                                "'{$row[1]}'",        // LineType
                                "'{$row[2]}'",        // ProductCode
                                "'". preg_replace(
                                    [
                                        '/[^\x00-\x7F\xA0-\xFF]/u',   // Remove invalid characters
                                        "/\\\\/",                       // Escape backslashes
                                        "/'/",                         // Escape single quotes
                                        '/(?<!\\\\)\/(?=\d)/'           // Escape forward slash followed by a number (negative lookbehind for escaped backslash)
                                    ],
                                    [
                                        '', 
                                        '\\\\', 
                                        "''", 
                                        '\/'
                                    ],
                                    $row[3]
                                ) . "'",
                                "'{$row[4]}'",        // Location
                                "'{$row[5]}'",        // StockUOM
                                "'{$row[6]}'",        // OrderUOM
                                "'{$row[7]}'",        // Qty
                                "'{$row[8]}'",        // UnitCost
                                "'{$row[9]}'",        // Amount
                                "'{$row[10]}'",       // Disc1
                                "'{$row[11]}'",       // Disc2
                                "'{$row[12]}'",       // Disc3
                                "'{$row[13]}'",       // Disc4
                                "'{$row[14]}'",       // Disc5
                                "'{$row[15]}'",       // Discount
                                "'{$row[16]}'",       // NetAmount
                                "'{$row[17]}'",       // RowNum
                                "'{$row[18]}'",       // Ref1
                                "'{$row[19]}'",       // Ref2
                                "'{$row[20]}'",       // Ref3
                                "'{$row[21]}'",       // RemQty
                                "'{$row[22]}'",       // CustomerCode
                                "'{$row[23]}'",       // Distributor
                                "'{$row[24]}'",       // RATE1
                                "'{$row[25]}'",       // RATE2
                                "'{$row[26]}'",       // RATE3
                                "'{$row[27]}'",       // RATE1BASIS
                                "'{$row[28]}'",       // RATE2BASIS
                                "'{$row[29]}'"        // RATE3BASIS
                            ];
                
                            $insertScript[] = "INSERT INTO SPM.SOLINH (DocNo, LineType, ProductCode, Description, Location, StockUOM, OrderUOM, Qty, UnitCost, Amount, Disc1, Disc2, Disc3, Disc4, Disc5, Discount, NetAmount, RowNum, Ref1, Ref2, Ref3, RemQty, CustomerCode, Distributor, RATE1, RATE2, RATE3, RATE1BASIS, RATE2BASIS, RATE3BASIS) VALUES (" . implode(',', $insertValues) . ");";
                        }
                        $rowCount++;
                    }
                
                    $insertScript[] = "ROLLBACK TRAN;"; // End the transaction in the script
                
                    // Write INSERT statements to file
                    $outputFilename = 'solinh_insert_script.sql';
                    $filePath = base_path('app/Console/Commands/' . $outputFilename);
                    file_put_contents($filePath, implode("\n", $insertScript));
                
                    $this->info("Insert script successfully generated at: $filePath");
                
                } catch (\Exception $e) {
                    $this->error("Error generating insert script: {$e->getMessage()}");
                }
                break;
            case "SODRHDR":
                    if (!file_exists($filepath)) {
                        $this->error("File not found: $filepath");
                        return;
                    }
    
                    $file = new SplFileObject($filepath, 'r');
                    $file->setFlags(SplFileObject::READ_CSV);
                    $file->setCsvControl('|'); // Pipe delimiter
    
                    // Skip header row
                    $file->current(); 
                    $file->next();
            
                    try {
                        $rowCount = 0;
                        $insertScript = ["BEGIN TRAN;"];
                    
                        foreach ($file as $row) {
                            if ($rowCount > 0 && $row && !empty($row[0])) {
                                $insertValues = [
                                    "'{$row[0]}'",        // Docno
                                    $this->formatDate($row[1]),        // Docdate
                                    "'{$row[2]}'",        // Customercode
                                    "'{$row[3]}'",        // Discountcode
                                    "'{$row[4]}'",        // Shipto
                                    "'{$row[5]}'",        // Poref
                                    "'{$row[6]}'",        // Paymentcode
                                    "'{$row[7]}'",        // Dropship
                                    "'{$row[8]}'",        // Procstat
                                    $this->formatDate($row[9]),          // Reqdate
                                    "'{$row[10]}'",       // Totalsales
                                    "'{$row[11]}'",       // Lessdiscount
                                    "'{$row[12]}'",       // Salesdiscount
                                    "'{$row[13]}'",       // Addvat
                                    "'{$row[14]}'",       // Grandtotal
                                    "'{$row[15]}'",       // Spare1
                                    "'{$row[16]}'",       // Spare2
                                    "'{$row[17]}'",       // Spare3
                                    "'{$row[18]}'",       // Recuser
                                    $this->formatDate($row[19]),          // Recdate
                                    "'{$row[20]}'",       // Moduser
                                    $this->formatDate($row[21]),          // Moddate
                                    "'{$row[22]}'",       // Postuser
                                    $this->formatDate($row[23]),          // Postdate
                                    "'{$row[24]}'",       // Printuser
                                    $this->formatDate($row[25]),          // Printdate
                                    "'{$row[26]}'",       // Sino
                                    $this->formatDate($row[27]),          // Sidate
                                    $this->formatDate($row[28]),          // Duedate
                                    "'{$row[29]}'",       // Printaxuser
                                    $this->formatDate($row[30]),          // Printaxdate
                                    "'{$row[31]}'",       // Remarks
                                    "'{$row[32]}'",       // Grandhome
                                    "'{$row[33]}'",       // NoSoref
                                    "'{$row[34]}'",       // SiRef
                                    "'{$row[35]}'",       // DRRef
                                    "'{$row[36]}'",       // TransactionRef
                                    "'{$row[37]}'",       // Distributor
                                    "'{$row[39]}'",       // Salesman
                                    "'{$row[40]}'",       // AmountPaid
                                    "'{$row[41]}'",       // EWT
                                    "'{$row[42]}'",       // ORNo
                                ];
                    
                                // Explicitly list column names to match the $insertValues array
                                $columns = "Docno, Docdate, Customercode, Discountcode, Shipto, Poref, Paymentcode, Dropship, Procstat, Reqdate, Totalsales, Lessdiscount, Salesdiscount, Addvat, Grandtotal, Spare1, Spare2, Spare3, Recuser, Recdate, Moduser, Moddate, Postuser, Postdate, Printuser, Printdate, Sino, Sidate, Duedate, Printaxuser, Printaxdate, Remarks, Grandhome, NoSoref, SiRef, DRRef, TransactionRef, Distributor, Salesman, AmountPaid, EWT, ORNo";
                                $insertScript[] = "INSERT INTO SPM.SODRHDR ($columns) VALUES (" . implode(',', $insertValues) . ");";
                            }
                            $rowCount++;
                        }
                    
                        $insertScript[] = "ROLLBACK TRAN;"; // End the transaction in the script
                    
                        // Write INSERT statements to file
                        $outputFilename = 'SODRHDR_insert_script.sql';
                        $filePath = base_path('app/Console/Commands/' . $outputFilename);
                        file_put_contents($filePath, implode("\n", $insertScript));
                    
                        $this->info("Insert script successfully generated at: $filePath");
                    
                    } catch (\Exception $e) {
                        $this->error("Error generating insert script: {$e->getMessage()}");
                    }
                    break;
            case "SODRLIN":
                    if (!file_exists($filepath)) {
                        $this->error("File not found: $filepath");
                        return;
                    }

                    $file = new SplFileObject($filepath, 'r');
                    $file->setFlags(SplFileObject::READ_CSV);
                    $file->setCsvControl('|'); // Pipe delimiter

                    // Skip header row
                    $file->current(); 
                    $file->next();
            
                    try {
                        $rowCount = 0;
                        $insertScript = ["BEGIN TRAN;"]; // Start the transaction in the script
                    
                        foreach ($file as $row) {
                            if ($rowCount > 0 && $row && !empty($row[0])) {
                                $insertValues = [
                                    "'{$row[0]}'",        // Docno
                                    "'{$row[1]}'",        // Linetype
                                    "'{$row[2]}'",        // Productcode
                                    "'{$row[3]}'",        // Description
                                    "'{$row[4]}'",        // Location
                                    "'{$row[5]}'",        // Stockuom
                                    "'{$row[6]}'",        // Orderuom
                                    "'{$row[7]}'",        // Qty
                                    "'{$row[8]}'",        // Delqty
                                    "'{$row[9]}'",        // Backqty
                                    "'{$row[10]}'",       // Unitcost
                                    "'{$row[11]}'",       // Amount
                                    "'{$row[12]}'",       // Disc1
                                    "'{$row[13]}'",       // Disc2
                                    "'{$row[14]}'",       // Disc3
                                    "'{$row[15]}'",       // Disc4
                                    "'{$row[16]}'",       // Disc5
                                    "'{$row[17]}'",       // Discount
                                    "'{$row[18]}'",       // NetAmount
                                    "'{$row[19]}'",       // Rownum
                                    "'{$row[20]}'",       // Ref1
                                    "'{$row[21]}'",       // Ref2
                                    "'{$row[22]}'",       // Ref3
                                    "'{$row[23]}'",       // Acctcode
                                    "'{$row[24]}'",       // Sorownum
                                    "'{$row[25]}'",       // Delpc
                                    "'{$row[26]}'",       // Rempc
                                    "'{$row[27]}'",       // Avecost
                                    (isset($row[28]) && !empty($row[28])) ? "CONVERT(datetime, '" . date('Y-m-d H:i:s', strtotime($row[28])) . "', 120)" : 'NULL', // ExpiryDate
                                    "'{$row[29]}'",       // ExpYear
                                    "'{$row[30]}'",       // ExpMonth
                                    "'{$row[31]}'",       // ExpDay
                                    "'{$row[32]}'",       // MonthDay
                                    "'{$row[33]}'",       // PromoQty
                                    "'{$row[34]}'",       // Distributor
                                    "'{$row[35]}'",       // AllocationNo
                                    "'{$row[36]}'",       // RATE1
                                    "'{$row[37]}'",       // RATE2
                                    "'{$row[38]}'",       // RATE3
                                    "'{$row[39]}'",       // RATE1BASIS
                                    "'{$row[40]}'",       // RATE2BASIS
                                    "'{$row[41]}'",       // RATE3BASIS
                                    "'{$row[42]}'",       // BatchNo
                                ];
                    
                                // Explicitly list column names to match the $insertValues array
                                $columns = "Docno, Linetype, Productcode, Description, Location, Stockuom, Orderuom, Qty, Delqty, Backqty, Unitcost, Amount, Disc1, Disc2, Disc3, Disc4, Disc5, Discount, NetAmount, Rownum, Ref1, Ref2, Ref3, Acctcode, Sorownum, Delpc, Rempc, Avecost, ExpiryDate, ExpYear, ExpMonth, ExpDay, MonthDay, PromoQty, Distributor, AllocationNo, RATE1, RATE2, RATE3, RATE1BASIS, RATE2BASIS, RATE3BASIS, BatchNo";
                                $insertScript[] = "INSERT INTO SPM.SODRLIN ($columns) VALUES (" . implode(',', $insertValues) . ");";
                            }
                            $rowCount++;
                        }
                    
                        $insertScript[] = "ROLLBACK TRAN;"; // End the transaction in the script
                    
                        // Write INSERT statements to file
                        $outputFilename = 'sodrlin_insert_script.sql'; 
                        $filePath = base_path('app/Console/Commands/' . $outputFilename);
                        file_put_contents($filePath, implode("\n", $insertScript));
                    
                        $this->info("Insert script successfully generated at: $filePath");
                    
                    } catch (\Exception $e) {
                        $this->error("Error generating insert script: {$e->getMessage()}");
                    }
                    break;
            case "SORETURNHDR":
                if (!file_exists($filepath)) {
                    $this->error("File not found: $filepath");
                    return;
                }

                $file = new SplFileObject($filepath, 'r');
                $file->setFlags(SplFileObject::READ_CSV);
                $file->setCsvControl('|'); // Pipe delimiter

                // Skip header row
                $file->current(); 
                $file->next();
        
                try {
                    
                    // Wrap the operation in a database transaction
                    $rowCount = 0;
                    DB::beginTransaction();
                    foreach($file as $row) {
                        if ($rowCount > 0) {
                            if ($row && !empty($row[0])) {
                                // Create and save using the Eloquent model
                                // SORETURNHDR:: where('Docno', $row[0])
                                //         ->where('Distributor', $row[34])
                                //         ->delete();

                                SORETURNHDR::create([
                                    'Docno'         => $row[0]  ?? null,
                                    'Docdate'       => $this->formatDate($row[1]),  
                                    'TranType'       => $row[2]  ?? null,
                                    'Customercode'  => $row[3]  ?? null,
                                    'Discountcode'  => $row[4]  ?? null,
                                    'Shipto'        => $row[5]  ?? null,
                                    'Poref'         => $row[6]  ?? null,
                                    'Paymentcode'   => $row[7]  ?? null,
                                    'Dropship'      => $row[8]  ?? null,
                                    'Procstat'      => $row[9]  ?? null,
                                    'Reqdate'       => $this->formatDate($row[10]), 
                                    'Totalsales'    => $row[11] ?? null,
                                    'Lessdiscount'  => $row[12] ?? null,
                                    'Salesdiscount' => $row[13] ?? null,
                                    'Addvat'        => $row[14] ?? null,
                                    'Grandtotal'    => $row[15] ?? null,
                                    'Spare1'        => $row[16] ?? null,
                                    'Spare2'        => $row[17] ?? null,
                                    'Spare3'        => $row[18] ?? null,
                                    'Recuser'       => $row[19] ?? null,
                                    'Recdate'       => $this->formatDate($row[20]), 
                                    'Moduser'       => $row[21] ?? null,
                                    'Moddate'       => $this->formatDate($row[22]), 
                                    'Postuser'      => $row[23] ?? null,
                                    'Postdate'      => $this->formatDate($row[24]), 
                                    'Printuser'     => $row[25] ?? null,
                                    'Printdate'     => $this->formatDate($row[26]),
                                    'Drno'          => $row[27] ?? null,
                                    'Drdate'        => $this->formatDate($row[28]), 
                                    'Sino'          => $row[29] ?? null,
                                    'Sidate'        => $this->formatDate($row[30]), 
                                    'Duedate'       => $this->formatDate($row[31]), 
                                    'Purpose'       => $row[32] ?? null,
                                    'Remarks'       => $row[33] ?? null,
                                    'Distributor'   => $row[34] ?? null,
                                    'Salesman'      => $row[35] ?? null
                                ]);
                            }
                        }
                        $rowCount++;
                    }
                    DB::commit();
                    $this->info("Rows successfully inserted into SPM.SORETURNHDR: $rowCount");

                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error inserting rows: {$e->getMessage()}");
                }
                break;
            case "UnappliedAmount":
                if (!file_exists($filepath)) {
                    $this->error("File not found: $filepath");
                    return;
                }

                $file = new SplFileObject($filepath, 'r');
                $file->setFlags(SplFileObject::READ_CSV);
                $file->setCsvControl('|'); // Pipe delimiter

                // Skip header row
                $file->current(); 
                $file->next();
        
                try {
                    
                    // Wrap the operation in a database transaction
                    $rowCount = 0;
                    DB::beginTransaction();
                    foreach($file as $row) {
                        if ($rowCount > 0) {
                            if ($row && !empty($row[0])) {
                                // Create and save using the Eloquent model
                                UnappliedAmount::create([
                                    'Docno'         => $row[0]  ?? null,
                                    'Applyto'       => $row[1]  ?? null,
                                    'Doctype'       => $row[2]  ?? null,
                                    'Customercode'  => $row[3]  ?? null,
                                    'Distributor'   => $row[4]  ?? null,
                                    'Salesman'      => $row[5]  ?? null,
                                    'Docdate'       => $this->formatDate($row[6]),
                                    'Duedate'       => $this->formatDate($row[7]),
                                    'Docamt'        => $row[8]  ?? null,
                                    'Recuser'       => $row[9]  ?? null,
                                    'Recdate'       => $this->formatDate($row[10]),
                                    'Dochome'       => $row[11] ?? null,
                                ]);
                            }
                        }
                        $rowCount++;
                    }
                    // Commit the transaction
                    DB::commit();
                    $this->info("Rows successfully inserted into SPM.UnappliedAmount: $rowCount");
                    // dd($counter);
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error inserting rows: {$e->getMessage()}");
                }
                break;
            case "AllocationAssignment":
                if (!file_exists($filepath)) {
                    $this->error("File not found: $filepath");
                    return;
                }

                $file = new SplFileObject($filepath, 'r');
                $file->setFlags(SplFileObject::READ_CSV);
                $file->setCsvControl('|'); // Pipe delimiter

                // Skip header row
                $file->current(); 
                $file->next();
        
                try {
                    
                    // Wrap the operation in a database transaction
                    $rowCount = 0;
                    DB::beginTransaction();
                    foreach($file as $row) {
                        if ($rowCount > 0) {
                            if ($row && !empty($row[0])) {
                                // Create and save using the Eloquent model
                                AllocationAssignment::create([
                                    'AllocationNo'     => $row[0]  ?? null,
                                    'AllocationDate'   => $this->formatDate($row[1]),  
                                    'SONo'             => $row[2]  ?? null,
                                    'SODate'           => $this->formatDate($row[3]),  
                                    'ReqDeliveryDate'  => $this->formatDate($row[4]),  
                                    'Distributor'      => $row[5]  ?? null,
                                    'ProductCode'      => $row[6]  ?? null,
                                    'Location'         => $row[7]  ?? null,
                                    'OrderQty'         => $row[8]  ?? null,
                                    'OrderUOM'         => $row[9]  ?? null,
                                    'AllocatedQty'     => $row[10] ?? null,
                                    'ExpiryDate'       => $this->formatDate($row[11]),  
                                    'ManualAllocQty'   => $row[12] ?? null,
                                    'ManualAllocUOM'   => $row[13] ?? null,
                                    'SORowNo'          => $row[14] ?? null,
                                    'RecUser'          => $row[15] ?? null,
                                    'RecDate'          => $this->formatDate($row[16]),  
                                    'ModUser'          => $row[17] ?? null,
                                    'ModDate'          => $this->formatDate($row[18]),  
                                    'PostStatus'       => $row[19] ?? null,
                                    'PostUser'         => $row[20] ?? null,
                                    'PostDate'         => $this->formatDate($row[21]),  
                                    'BatchNo'          => $row[34] ?? null,
                                ]);
                            }
                        }
                        $rowCount++;
                    }
                    // Commit the transaction
                    DB::commit();
                    $this->info("Rows successfully inserted into SPM.AllocationAssignment: $rowCount");
                    // dd($counter);
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error inserting rows: {$e->getMessage()}");
                }
                break;
            case "IVHDRH":
                if (!file_exists($filepath)) {
                    $this->error("File not found: $filepath");
                    return;
                }

                $file = new SplFileObject($filepath, 'r');
                $file->setFlags(SplFileObject::READ_CSV);
                $file->setCsvControl('|'); // Pipe delimiter

                // Skip header row
                $file->current(); 
                $file->next();
        
                try {
                    
                    // Wrap the operation in a database transaction
                    $rowCount = 0;
                    DB::beginTransaction();
                    foreach($file as $row) {
                        if ($rowCount > 0) {
                            if ($row && !empty($row[0])) {
                                // Create and save using the Eloquent model
                                IVHDRH::create([
                                    'Docno'        => $row[0]  ?? null,
                                    'Docdate'      => $this->formatDate($row[1]),
                                    'Desc1'        => $row[2]  ?? null,
                                    'Desc2'        => $row[3]  ?? null,
                                    'Trantype'     => $row[4]  ?? null,
                                    'BU'           => $row[5]  ?? null,
                                    'Supcode'      => $row[6]  ?? null,
                                    'Procstat'     => $row[7]  ?? null,
                                    'Spare1'       => $row[8]  ?? null,
                                    'Spare2'       => $row[9]  ?? null,
                                    'Spare3'       => $row[10] ?? null,
                                    'Recuser'      => $row[11] ?? null,
                                    'Recdate'      => $this->formatDate($row[12]),
                                    'Moduser'      => $row[13] ?? null,
                                    'Moddate'      => $this->formatDate($row[14]),
                                    'Postuser'     => $row[15] ?? null,
                                    'Postdate'     => $this->formatDate($row[16]),
                                    'Distributor'  => $row[17] ?? null,
                                    'ReferenceNo'  => $row[18] ?? null,
                                    'Customer'     => $row[19] ?? null,
                                    'Salesman'     => $row[20] ?? null,
                                ]);
                            }
                        }
                        $rowCount++;
                    }
                    // Commit the transaction
                    DB::commit();
                    $this->info("Rows successfully inserted into SPM.IVHDRH: $rowCount");
                    // dd($counter);
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error inserting rows: {$e->getMessage()}");
                }
                break;
            case "IVLINH":
                if (!file_exists($filepath)) {
                    $this->error("File not found: $filepath");
                    return;
                }

                $file = new SplFileObject($filepath, 'r');
                $file->setFlags(SplFileObject::READ_CSV);
                $file->setCsvControl('|'); // Pipe delimiter

                // Skip header row
                $file->current(); 
                $file->next();
        
                try {
                    
                    // Wrap the operation in a database transaction
                    $rowCount = 0;
                    DB::beginTransaction();
                    foreach($file as $row) {
                        if ($rowCount > 0) {
                            if ($row && !empty($row[0])) {
                                // Create and save using the Eloquent model
                                IVLINH::create([
                                    'Docno'             => $row[0]  ?? null,
                                    'Productcode'       => $row[1]  ?? null,
                                    'Docdate'           => $this->formatDate($row[2]),
                                    'Location'          => $row[3]  ?? null,
                                    'Uom'               => $row[4]  ?? null,
                                    'Tranuom'           => $row[5]  ?? null,
                                    'Soh'               => $row[6]  ?? null,
                                    'Qty'               => $row[7]  ?? null,
                                    'Unitcost'          => $row[8]  ?? null,
                                    'Amount'            => $row[9]  ?? null,
                                    'Avecost'           => $row[10] ?? null,
                                    'Rownum'            => $row[11] ?? null,
                                    'Eventid'           => $row[12] ?? null,
                                    'Trantype'          => $row[13] ?? null,
                                    'Ref2'             => $row[14] ?? null,
                                    'Ref3'             => $row[15] ?? null,
                                    'Currency'          => $row[16] ?? null,
                                    'Unitcosthme'       => $row[17] ?? null,
                                    'Totalhme'          => $row[18] ?? null,
                                    'ExpiryDate'        => $this->formatDate($row[19]),
                                    'ManufactureDate'   => $this->formatDate($row[20]),
                                    'Distributor'       => $row[21] ?? null,
                                    'BatchNo'           => $row[22] ?? null,
                                ]);
                            }
                        }
                        $rowCount++;
                    }
                    // Commit the transaction
                    DB::COMMIT();
                    $this->info("Rows successfully inserted into SPM.IVLINH: $rowCount");
                    // dd($counter);
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error inserting rows: {$e->getMessage()}");
                }
                break;
            case "SORETURNLIN":
                if (!file_exists($filepath)) {
                    $this->error("File not found: $filepath");
                    return;
                }

                $file = new SplFileObject($filepath, 'r');
                $file->setFlags(SplFileObject::READ_CSV);
                $file->setCsvControl('|'); // Pipe delimiter

                // Skip header row
                $file->current(); 
                $file->next();
        
                try {
                    
                    // Wrap the operation in a database transaction
                    $rowCount = 0;
                    DB::beginTransaction();
                    foreach($file as $row) {
                        if ($rowCount > 0) {
                            if ($row && !empty($row[0])) {
                                SORETURNLIN::create([
                                    'DocNo'               => $row[0]  ?? null,
                                    'LineType'            => $row[1]  ?? null,
                                    'ProductCode'         => $row[2]  ?? null,
                                    'Description'         => $row[3]  ?? null,
                                    'Location'            => $row[4]  ?? null,
                                    'StockUOM'            => $row[5]  ?? null,
                                    'OrderUOM'            => $row[6]  ?? null,
                                    'Qty'                => $row[7]  ?? null,
                                    'DelQty'              => $row[8]  ?? null,
                                    'ReturnQty'           => $row[9]  ?? null,
                                    'UnitCost'            => $row[10] ?? null,
                                    'Amount'              => $row[11] ?? null,
                                    'Disc1'               => $row[12] ?? null,
                                    'Disc2'               => $row[13] ?? null,
                                    'Disc3'               => $row[14] ?? null,
                                    'Disc4'               => $row[15] ?? null,
                                    'Disc5'               => $row[16] ?? null,
                                    'Discount'            => $row[17] ?? null,
                                    'RowNum'              => $row[18] ?? null,
                                    'Ref1'               => $row[19] ?? null,
                                    'Ref2'               => $row[20] ?? null,
                                    'Ref3'               => $row[21] ?? null,
                                    'AcctCode'            => $row[22] ?? null,
                                    'DelPC'               => $row[23] ?? null,
                                    'ReturnUOM'           => $row[24] ?? null,
                                    'AveCost'             => $row[25] ?? null,
                                    'DRRowNum'            => $row[26] ?? null,
                                    'RemPC'               => $row[27] ?? null,
                                    'ExpiryDate'         => $this->formatDate($row[28]),
                                    'ExpYear'            => $row[29] ?? null,
                                    'ExpMonth'           => $row[30] ?? null,
                                    'ExpDay'             => $row[31] ?? null,
                                    'BoQty'              => $row[32] ?? null,
                                    'BoUom'              => $row[33] ?? null,
                                    'BoUnitCost'         => $row[34] ?? null,
                                    'Purpose'            => $row[35] ?? null,
                                    'MonthDay'           => $row[36] ?? null,
                                    'NetAmount'           => $row[37] ?? null,
                                    'AppliedAmount'       => $row[38] ?? null,
                                    'Reason'             => $row[39] ?? null,
                                    'RefDocNo'           => $row[40] ?? null,
                                    'VAT'                => $row[41] ?? null,
                                    'TotalAmount'        => $row[42] ?? null,
                                    'DestinationLocation' => $row[43] ?? null,
                                    'Distributor'        => $row[44] ?? null,
                                    'RATE1'              => $row[45] ?? null,
                                    'RATE2'              => $row[46] ?? null,
                                    'RATE3'              => $row[47] ?? null,
                                    'RATE1BASIS'         => $row[48] ?? null,
                                    'RATE2BASIS'         => $row[49] ?? null,
                                    'RATE3BASIS'         => $row[50] ?? null,
                                    'BatchNo'            => $row[51] ?? null,
                                ]);
                            }
                        }
                        $rowCount++;
                    }
                    DB::commit();
                    $this->info("Rows successfully inserted into SPM.SORETURLIN: $rowCount");

                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error inserting rows: {$e->getMessage()}");
                }
                break;
            case "ReceiptsPrincipalHDR":
                if (!file_exists($filepath)) {
                    $this->error("File not found: $filepath");
                    return;
                }

                $file = new SplFileObject($filepath, 'r');
                $file->setFlags(SplFileObject::READ_CSV);
                $file->setCsvControl('|'); // Pipe delimiter

                // Skip header row
                $file->current(); 
                $file->next();
        
                try {
                    
                    // Wrap the operation in a database transaction
                    $rowCount = 0;
                    DB::beginTransaction();
                    foreach($file as $row) {
                        if ($rowCount > 0) {
                            if ($row && !empty($row[0])) {
                                ReceiptsPrincipalHDR::create([
                                    'RPTransNo'        => $row[0]  ?? null,
                                    'RPTransDate'      => $this->formatDate($row[1]),
                                    'Distributor'      => $row[2]  ?? null,
                                    'Principal'        => $row[3]  ?? null,
                                    'POTransNo'        => $row[4]  ?? null,
                                    'InvoiceNo'        => $row[5]  ?? null,
                                    'DRNo'             => $row[6]  ?? null,
                                    'Remarks'          => $row[7]  ?? null,
                                    'TotalCost'        => $row[8]  ?? null,
                                    'RecUser'          => $row[9]  ?? null,
                                    'RecDate'          => $this->formatDate($row[10]),
                                    'ModUser'          => $row[11] ?? null,
                                    'ModDate'          => $this->formatDate($row[12]),
                                    'PostStatus'       => $row[13] ?? null,
                                    'PostUser'         => $row[14] ?? null,
                                    'PostDate'         => $this->formatDate($row[15]),
                                    'Reason'           => $row[16] ?? null,
                                    'TransType'        => $row[17] ?? null,
                                    'ActualReceiptDate'=> $this->formatDate($row[18]),
                                ]);
                            }
                        }
                        $rowCount++;
                    }
                    DB::commit();
                    $this->info("Rows successfully inserted into SPM.ReceiptsPrincipalHDR: $rowCount");

                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error inserting rows: {$e->getMessage()}");
                }
                break;
            case "ReceiptsPrincipalLIN":
                if (!file_exists($filepath)) {
                    $this->error("File not found: $filepath");
                    return;
                }

                $file = new SplFileObject($filepath, 'r');
                $file->setFlags(SplFileObject::READ_CSV);
                $file->setCsvControl('|'); // Pipe delimiter

                // Skip header row
                $file->current(); 
                $file->next();
        
                try {
                    
                    // Wrap the operation in a database transaction
                    $rowCount = 0;
                    DB::beginTransaction();
                    foreach($file as $row) {
                        if ($rowCount > 0) {
                            if ($row && !empty($row[0])) {
                                ReceiptsPrincipalLIN::create([
                                    'RPTransNo'        => $row[0]  ?? null,
                                    'ProductCode'      => $row[1]  ?? null,
                                    'Location'         => $row[2]  ?? null,
                                    'SOH'              => $row[3]  ?? null,
                                    'StockUOM'         => $row[4]  ?? null,
                                    'Qty'              => $row[5]  ?? null,
                                    'ExpiryDate'       => $this->formatDate($row[6]), 
                                    'ExpiryYear'       => $row[7]  ?? null, // Assuming these are already year values
                                    'ExpiryMonth'      => $row[8]  ?? null, // Assuming these are already month values
                                    'ExpiryDay'        => $row[9]  ?? null, // Assuming these are already day values
                                    'MfgDate'          => $this->formatDate($row[10]), 
                                    'TransUOM'         => $row[11] ?? null,
                                    'UnitCost'         => $row[12] ?? null,
                                    'ShelfLife'        => $row[13] ?? null,
                                    'MorD'             => $row[14] ?? null,
                                    'TotalCost'        => $row[15] ?? null,
                                    'RowNo'            => $row[16] ?? null,
                                    'Distributor'      => $row[17] ?? null,
                                    'BatchNo'          => $row[18] ?? null,
                                ]);
                            }
                        }
                        $rowCount++;
                    }
                    DB::commit();
                    $this->info("Rows successfully inserted into SPM.ReceiptsPrincipalLIN: $rowCount");

                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error inserting rows: {$e->getMessage()}");
                }
                break;
            case "SOCreditCheck":
                if (!file_exists($filepath)) {
                    $this->error("File not found: $filepath");
                    return;
                }

                $file = new SplFileObject($filepath, 'r');
                $file->setFlags(SplFileObject::READ_CSV);
                $file->setCsvControl('|'); // Pipe delimiter

                // Skip header row
                $file->current(); 
                $file->next();
        
                try {
                    $rowCount = 0;
                    $insertScript = ["BEGIN TRAN;"];  // Start the transaction in the script
                
                    foreach ($file as $row) {
                        if ($rowCount > 0 && $row && !empty($row[0])) {
                            // Generate INSERT statement
                            $insertValues = [
                                "'{$row[0]}'",        // SONo
                                "'{$row[1]}'",        // CustomerCode
                                "'{$row[2]}'",        // CreditLimit
                                "'{$row[3]}'",        // AvailableForSO
                                "'{$row[4]}'",        // OpenApprovedSO
                                "'{$row[5]}'",        // PendingDR
                                "'{$row[6]}'",        // ARAmount
                                "'{$row[7]}'",        // ApprovedDR
                                "'{$row[8]}'",        // ApprovedDM
                                "'{$row[9]}'",        // ApprovedCM
                                "'{$row[10]}'",       // ApprovedARBegBal
                                "'{$row[11]}'",       // ApprovedCol
                                "'{$row[12]}'",       // ApprovedColPDC
                                "'{$row[13]}'",       // ReturnofGoodStock
                                "'{$row[14]}'",       // ReturnofBadStock
                                "'{$row[15]}'",       // DMQAJ
                                "'{$row[16]}'",       // CMQAJ
                                "'{$row[17]}'",       // PrePayment
                                "'{$row[18]}'",       // BaseAging 
                                "'{$row[19]}'",       // ActualAging 
                                "'{$row[20]}'",       // CreditStatus
                                "'{$row[21]}'",       // AgingStatus
                                "'{$row[22]}'",       // Distributor
                            ];
                
                            // List all columns explicitly
                            $columns = "SONo, CustomerCode, CreditLimit, AvailableForSO, OpenApprovedSO, PendingDR, ARAmount, ApprovedDR, ApprovedDM, ApprovedCM, ApprovedARBegBal, ApprovedCol, ApprovedColPDC, ReturnofGoodStock, ReturnofBadStock, DMQAJ, CMQAJ, PrePayment, BaseAging, ActualAging, CreditStatus, AgingStatus, Distributor";
                            $insertScript[] = "INSERT INTO SPM.SOCreditCheck ($columns) VALUES (" . implode(',', $insertValues) . ");";
                        }
                        $rowCount++;
                    }
                    $insertScript[] = "ROLLBACK TRAN;"; // End the transaction in the script
                
                    // Write INSERT statements to file
                    $outputFilename = 'socreditcheck_insert_script.sql';
                    $filePath = base_path('app/Console/Commands/' . $outputFilename);
                    file_put_contents($filePath, implode("\n", $insertScript));
                
                    $this->info("Insert script successfully generated at: $filePath");
                
                } catch (\Exception $e) {
                    $this->error("Error generating insert script: {$e->getMessage()}");
                }
                break;
            default :
                
        }
    }
}
