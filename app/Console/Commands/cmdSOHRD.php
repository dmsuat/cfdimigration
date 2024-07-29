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
                                        "'{$row[1]}'",        // Sono 
                                        $this->formatDate($row[2]),    // Docdate
                                        "'{$row[3]}'",        // Customercode
                                        "'{$row[4]}'",        // Discountcode
                                        "'{$row[5]}'",        // Shipto
                                        "'{$row[6]}'",        // Poref
                                        "'{$row[7]}'",        // Paymentcode
                                        "'{$row[8]}'",        // Dropship
                                        "'{$row[9]}'",        // Procstat
                                        $this->formatDate($row[10]),  // Reqdate
                                        "'{$row[11]}'",       // Totalsales
                                        "'{$row[12]}'",       // Lessdiscount
                                        "'{$row[13]}'",       // Salesdiscount
                                        "'{$row[14]}'",       // Addvat
                                        "'{$row[15]}'",       // Grandtotal
                                        "'{$row[16]}'",       // Spare1
                                        "'{$row[17]}'",       // Spare2
                                        "'{$row[18]}'",       // Spare3
                                        "'{$row[19]}'",       // Recuser
                                        $this->formatDate($row[20]),  // Recdate
                                        "'{$row[21]}'",       // Moduser
                                        $this->formatDate($row[22]),  // Moddate
                                        "'{$row[23]}'",       // Postuser
                                        $this->formatDate($row[24]),  // Postdate
                                        "'{$row[25]}'",       // Printuser
                                        $this->formatDate($row[26]),  // Printdate
                                        "'{$row[27]}'",       // Sino
                                        $this->formatDate($row[28]),  // Sidate
                                        $this->formatDate($row[29]),  // Duedate
                                        "'{$row[30]}'",       // Printaxuser
                                        $this->formatDate($row[31]),  // Printaxdate
                                        "'{$row[32]}'",       // Remarks
                                        "'{$row[33]}'",       // Grandhome
                                        "'{$row[34]}'",       // NoSoref
                                        "'{$row[35]}'",       // SiRef
                                        "'{$row[36]}'",       // DRRef
                                        "'{$row[37]}'",       // TransactionRef
                                        "'{$row[38]}'",       // Distributor
                                        "'{$row[39]}'",       // Salesman
                                        "'{$row[40]}'",       // AmountPaid
                                        "'{$row[41]}'",       // EWT
                                        "'{$row[42]}'",       // ORNo
                                ];
                    
                                // Explicitly list column names to match the $insertValues array, including Sono
                                $columns = "Docno, Sono, Docdate, Customercode, Discountcode, Shipto, Poref, Paymentcode, Dropship, Procstat, Reqdate, Totalsales, Lessdiscount, Salesdiscount, Addvat, Grandtotal, Spare1, Spare2, Spare3, Recuser, Recdate, Moduser, Moddate, Postuser, Postdate, Printuser, Printdate, Sino, Sidate, Duedate, Printaxuser, Printaxdate, Remarks, Grandhome, NoSoref, SiRef, DRRef, TransactionRef, Distributor, Salesman, AmountPaid, EWT, ORNo"; 
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
                    $rowCount = 0;
                    $insertScript = ["BEGIN TRAN;"];
                
                    foreach ($file as $row) {
                        if ($rowCount > 0 && $row && !empty($row[0])) {
                            $insertValues = [
                                "'{$row[0]}'",        // Docno
                                $this->formatDate($row[1]),        // Docdate
                                "'{$row[2]}'",        // TranType
                                "'{$row[3]}'",        // Customercode
                                "'{$row[4]}'",        // Discountcode
                                "'{$row[5]}'",        // Shipto
                                "'{$row[6]}'",        // Poref
                                "'{$row[7]}'",        // Paymentcode
                                "'{$row[8]}'",        // Dropship
                                "'{$row[9]}'",        // Procstat
                                $this->formatDate($row[10]),  // Reqdate
                                "'{$row[11]}'",       // Totalsales
                                "'{$row[12]}'",       // Lessdiscount
                                "'{$row[13]}'",       // Salesdiscount
                                "'{$row[14]}'",       // Addvat
                                "'{$row[15]}'",       // Grandtotal
                                "'{$row[16]}'",       // Spare1
                                "'{$row[17]}'",       // Spare2
                                "'{$row[18]}'",       // Spare3
                                "'{$row[19]}'",       // Recuser
                                $this->formatDate($row[20]),  // Recdate
                                "'{$row[21]}'",       // Moduser
                                $this->formatDate($row[22]),  // Moddate
                                "'{$row[23]}'",       // Postuser
                                $this->formatDate($row[24]),  // Postdate
                                "'{$row[25]}'",       // Printuser
                                $this->formatDate($row[26]),  // Printdate
                                "'{$row[27]}'",       // Drno
                                $this->formatDate($row[28]),  // Drdate
                                "'{$row[29]}'",       // Sino
                                $this->formatDate($row[30]),  // Sidate
                                $this->formatDate($row[31]),  // Duedate
                                "'{$row[32]}'",       // Purpose
                                "'{$row[33]}'",       // Remarks 
                                "'{$row[34]}'",       // Distributor
                                "'{$row[35]}'",       // Salesman
                            ];
                
                            // Explicitly list column names to match the $insertValues array
                            $columns = "Docno, Docdate, TranType, Customercode, Discountcode, Shipto, Poref, Paymentcode, Dropship, Procstat, Reqdate, Totalsales, Lessdiscount, Salesdiscount, Addvat, Grandtotal, Spare1, Spare2, Spare3, Recuser, Recdate, Moduser, Moddate, Postuser, Postdate, Printuser, Printdate, Drno, Drdate, Sino, Sidate, Duedate, Purpose, Remarks, Distributor, Salesman";
                            $insertScript[] = "INSERT INTO SPM.SORETURNHDR ($columns) VALUES (" . implode(',', $insertValues) . ");";
                        }
                        $rowCount++;
                    }
                
                    $insertScript[] = "ROLLBACK TRAN;"; // End the transaction in the script
                   
                    // Write INSERT statements to file
                    $outputFilename = 'SORETURNHDR_insert_script.sql'; 
                    $filePath = base_path('app/Console/Commands/' . $outputFilename);
                    file_put_contents($filePath, implode("\n", $insertScript));
                
                    $this->info("Insert script successfully generated at: $filePath");
                
                } catch (\Exception $e) {
                    $this->error("Error generating insert script: {$e->getMessage()}");
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
                    $rowCount = 0;
                    $insertScript = ["BEGIN TRAN;"];
                
                    foreach ($file as $row) {
                        if ($rowCount > 0 && $row && !empty($row[0])) {
                            $insertValues = [
                                "'{$row[0]}'",        // Docno
                                "'{$row[1]}'",        // Applyto
                                "'{$row[2]}'",        // Doctype
                                "'{$row[3]}'",        // Customercode
                                "'{$row[4]}'",        // Distributor
                                "'{$row[5]}'",        // Salesman
                                $this->formatDate($row[6]),    // Docdate
                                $this->formatDate($row[7]),    // Duedate
                                "'{$row[8]}'",        // Docamt
                                "'{$row[9]}'",        // Recuser
                                $this->formatDate($row[10]),    // Recdate
                                "'{$row[11]}'",       // Dochome
                            ];
                
                            // Explicitly list column names to match the $insertValues array
                            $columns = "Docno, Applyto, Doctype, Customercode, Distributor, Salesman, Docdate, Duedate, Docamt, Recuser, Recdate, Dochome";
                            $insertScript[] = "INSERT INTO SPM.UnappliedAmount ($columns) VALUES (" . implode(',', $insertValues) . ");";
                        }
                        $rowCount++;
                    }
                
                    $insertScript[] = "ROLLBACK TRAN;"; // End the transaction in the script
                
                    // Write INSERT statements to file
                    $outputFilename = 'unappliedamount_insert_script.sql';
                    $filePath = base_path('app/Console/Commands/' . $outputFilename);
                    file_put_contents($filePath, implode("\n", $insertScript));
                
                    $this->info("Insert script successfully generated at: $filePath");
                
                } catch (\Exception $e) {
                    $this->error("Error generating insert script: {$e->getMessage()}");
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
                    $rowCount = 0;
                    $insertScript = ["BEGIN TRAN;"];
                
                    foreach ($file as $row) {
                        if ($rowCount > 0 && $row && !empty($row[0])) {
                            $insertValues = [
                                "'{$row[0]}'",        // AllocationNo
                                $this->formatDate($row[1]),        // AllocationDate
                                "'{$row[2]}'",        // SONo
                                $this->formatDate($row[3]),        // SODate
                                $this->formatDate($row[4]),        // ReqDeliveryDate
                                "'{$row[5]}'",        // Distributor
                                "'{$row[6]}'",        // ProductCode
                                "'{$row[7]}'",        // Location
                                "'{$row[8]}'",        // OrderQty
                                "'{$row[9]}'",        // OrderUOM
                                "'{$row[10]}'",       // AllocatedQty
                                $this->formatDate($row[11]),       // ExpiryDate
                                "'{$row[12]}'",       // ManualAllocQty
                                "'{$row[13]}'",       // ManualAllocUOM
                                "'{$row[14]}'",       // SORowNo
                                "'{$row[15]}'",       // RecUser
                                $this->formatDate($row[16]),       // RecDate
                                "'{$row[17]}'",       // ModUser
                                $this->formatDate($row[18]),       // ModDate
                                "'{$row[19]}'",       // PostStatus
                                "'{$row[20]}'",       // PostUser
                                $this->formatDate($row[21]),       // PostDate
                                "'{$row[34]}'",       // BatchNo 
                            ]; // Notice the jump from index 21 to 34, since those fields are not present
                
                            // Explicitly list column names to match the $insertValues array
                            $columns = "AllocationNo, AllocationDate, SONo, SODate, ReqDeliveryDate, Distributor, ProductCode, Location, OrderQty, OrderUOM, AllocatedQty, ExpiryDate, ManualAllocQty, ManualAllocUOM, SORowNo, RecUser, RecDate, ModUser, ModDate, PostStatus, PostUser, PostDate, BatchNo";
                            $insertScript[] = "INSERT INTO SPM.AllocationAssignment ($columns) VALUES (" . implode(',', $insertValues) . ");";
                        }
                        $rowCount++;
                    }
                
                    $insertScript[] = "ROLLBACK TRAN;"; // End the transaction in the script
                
                    // Write INSERT statements to file
                    $outputFilename = 'allocationassignment_insert_script.sql';
                    $filePath = base_path('app/Console/Commands/' . $outputFilename);
                    file_put_contents($filePath, implode("\n", $insertScript));
                
                    $this->info("Insert script successfully generated at: $filePath");
                
                } catch (\Exception $e) {
                    $this->error("Error generating insert script: {$e->getMessage()}");
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
                    $rowCount = 0;
                    $insertScript = ["BEGIN TRAN;"];
                
                    foreach ($file as $row) {
                        if ($rowCount > 0 && $row && !empty($row[0])) {
                            // Clean and escape Description in one step
                            $description = preg_replace(
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
                                $row[2]
                            );
                
                            $desc2 = preg_replace(
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
                            );
                
                            // Generate INSERT statement
                            $insertValues = [
                                "'{$row[0]}'",        // Docno
                                $this->formatDate($row[1]),        // Docdate
                                "'$description'",    // Desc1  (escape description for sql injection)
                                "'$desc2'",     // Desc2  (escape description for sql injection)
                                "'{$row[4]}'",        // Trantype
                                "'{$row[5]}'",        // BU
                                "'{$row[6]}'",        // Supcode
                                "'{$row[7]}'",        // Procstat
                                "'{$row[8]}'",        // Spare1
                                "'{$row[9]}'",        // Spare2
                                "'{$row[10]}'",       // Spare3
                                "'{$row[11]}'",       // Recuser
                                $this->formatDate($row[12]),       // Recdate
                                "'{$row[13]}'",       // Moduser
                                $this->formatDate($row[14]),       // Moddate
                                "'{$row[15]}'",       // Postuser
                                $this->formatDate($row[16]),       // Postdate
                                "'{$row[17]}'",       // Distributor
                                "'{$row[18]}'",       // ReferenceNo
                                "'{$row[19]}'",       // Customer
                                "'{$row[20]}'",       // Salesman
                            ];
                
                            // Explicitly list column names to match the $insertValues array
                            $columns = "Docno, Docdate, Desc1, Desc2, Trantype, BU, Supcode, Procstat, Spare1, Spare2, Spare3, Recuser, Recdate, Moduser, Moddate, Postuser, Postdate, Distributor, ReferenceNo, Customer, Salesman";
                            $insertScript[] = "INSERT INTO SPM.IVHDRH ($columns) VALUES (" . implode(',', $insertValues) . ");";
                        }
                        $rowCount++;
                    }
                
                    $insertScript[] = "ROLLBACK TRAN;"; // End the transaction in the script
                
                    // Write INSERT statements to file
                    $outputFilename = 'ivhdrh_insert_script.sql';
                    $filePath = base_path('app/Console/Commands/' . $outputFilename);
                    file_put_contents($filePath, implode("\n", $insertScript));
                
                    $this->info("Insert script successfully generated at: $filePath");
                
                } catch (\Exception $e) {
                    $this->error("Error generating insert script: {$e->getMessage()}");
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
                    $rowCount = 0;
                    $insertScript = ["BEGIN TRAN;"];
                
                    foreach ($file as $row) {
                        if ($rowCount > 0 && $row && !empty($row[0])) {
                            $insertValues = [
                                "'{$row[0]}'",        // Docno
                                "'{$row[1]}'",        // Productcode
                                $this->formatDate($row[2]),    // Docdate
                                "'{$row[3]}'",        // Location
                                "'{$row[4]}'",        // Uom
                                "'{$row[5]}'",        // Tranuom
                                "'{$row[6]}'",        // Soh
                                "'{$row[7]}'",        // Qty
                                "'{$row[8]}'",        // Unitcost
                                "'{$row[9]}'",        // Amount
                                "'{$row[10]}'",       // Avecost
                                "'{$row[11]}'",       // Rownum
                                "'{$row[12]}'",       // Eventid
                                "'{$row[13]}'",       // Trantype
                                "'{$row[14]}'",       // Ref2
                                "'{$row[15]}'",       // Ref3
                                "'{$row[16]}'",       // Currency
                                "'{$row[17]}'",       // Unitcosthme
                                "'{$row[18]}'",       // Totalhme
                                $this->formatDate($row[19]),       // ExpiryDate
                                $this->formatDate($row[20]),       // ManufactureDate
                                "'{$row[21]}'",       // Distributor
                                "'{$row[22]}'",       // BatchNo
                            ];
                
                            // Explicitly list column names to match the $insertValues array
                            $columns = "Docno, Productcode, Docdate, Location, Uom, Tranuom, Soh, Qty, Unitcost, Amount, Avecost, Rownum, Eventid, Trantype, Ref2, Ref3, Currency, Unitcosthme, Totalhme, ExpiryDate, ManufactureDate, Distributor, BatchNo";
                            $insertScript[] = "INSERT INTO SPM.IVLINH ($columns) VALUES (" . implode(',', $insertValues) . ");";
                        }
                        $rowCount++;
                    }
                
                    $insertScript[] = "ROLLBACK TRAN;"; // End the transaction in the script
                
                    // Write INSERT statements to file
                    $outputFilename = 'ivlinh_insert_script.sql';
                    $filePath = base_path('app/Console/Commands/' . $outputFilename);
                    file_put_contents($filePath, implode("\n", $insertScript));
                
                    $this->info("Insert script successfully generated at: $filePath");
                
                } catch (\Exception $e) {
                    $this->error("Error generating insert script: {$e->getMessage()}");
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
                                "'{$row[8]}'",        // DelQty
                                "'{$row[9]}'",        // ReturnQty
                                "'{$row[10]}'",       // UnitCost
                                "'{$row[11]}'",       // Amount
                                "'{$row[12]}'",       // Disc1
                                "'{$row[13]}'",       // Disc2
                                "'{$row[14]}'",       // Disc3
                                "'{$row[15]}'",       // Disc4
                                "'{$row[16]}'",       // Disc5
                                "'{$row[17]}'",       // Discount
                                "'{$row[18]}'",       // RowNum
                                "'{$row[19]}'",       // Ref1
                                "'{$row[20]}'",       // Ref2
                                "'{$row[21]}'",       // Ref3
                                "'{$row[22]}'",       // AcctCode
                                "'{$row[23]}'",       // DelPC
                                "'{$row[24]}'",       // ReturnUOM
                                "'{$row[25]}'",       // AveCost
                                "'{$row[26]}'",       // DRRowNum
                                "'{$row[27]}'",       // RemPC
                                $this->formatDate($row[28]),       // ExpiryDate
                                "'{$row[29]}'",       // ExpYear
                                "'{$row[30]}'",       // ExpMonth
                                "'{$row[31]}'",       // ExpDay
                                "'{$row[32]}'",       // BoQty
                                "'{$row[33]}'",       // BoUom
                                "'{$row[34]}'",       // BoUnitCost
                                "'{$row[35]}'",       // Purpose
                                "'{$row[36]}'",       // MonthDay
                                "'{$row[37]}'",       // NetAmount
                                "'{$row[38]}'",       // AppliedAmount
                                "'{$row[39]}'",       // Reason
                                "'{$row[40]}'",       // RefDocNo
                                "'{$row[41]}'",       // VAT
                                "'{$row[42]}'",       // TotalAmount
                                "'{$row[43]}'",       // DestinationLocation
                                "'{$row[44]}'",       // Distributor
                                "'{$row[45]}'",       // RATE1
                                "'{$row[46]}'",       // RATE2
                                "'{$row[47]}'",       // RATE3
                                "'{$row[48]}'",       // RATE1BASIS
                                "'{$row[49]}'",       // RATE2BASIS
                                "'{$row[50]}'",       // RATE3BASIS
                                "'{$row[51]}'",       // BatchNo
                            ];
                
                            // Explicitly list column names to match the $insertValues array
                            $columns = "DocNo, LineType, ProductCode, Description, Location, StockUOM, OrderUOM, Qty, DelQty, ReturnQty, UnitCost, Amount, Disc1, Disc2, Disc3, Disc4, Disc5, Discount, RowNum, Ref1, Ref2, Ref3, AcctCode, DelPC, ReturnUOM, AveCost, DRRowNum, RemPC, ExpiryDate, ExpYear, ExpMonth, ExpDay, BoQty, BoUom, BoUnitCost, Purpose, MonthDay, NetAmount, AppliedAmount, Reason, RefDocNo, VAT, TotalAmount, DestinationLocation, Distributor, RATE1, RATE2, RATE3, RATE1BASIS, RATE2BASIS, RATE3BASIS, BatchNo";
                
                            $insertScript[] = "INSERT INTO SPM.SORETURNLIN ($columns) VALUES (" . implode(',', $insertValues) . ");";
                        }
                        $rowCount++;
                    }
                    $insertScript[] = "ROLLBACK TRAN;"; // End the transaction in the script
                
                    // Write INSERT statements to file
                    $outputFilename = 'soreturnlin_insert_script.sql';
                    $filePath = base_path('app/Console/Commands/' . $outputFilename);
                    file_put_contents($filePath, implode("\n", $insertScript));
                
                    $this->info("Insert script successfully generated at: $filePath");
                
                } catch (\Exception $e) {
                    $this->error("Error generating insert script: {$e->getMessage()}");
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
                    $rowCount = 0;
                    $insertScript = ["BEGIN TRAN;"];
                
                    foreach ($file as $row) {
                        if ($rowCount > 0 && $row && !empty($row[0])) {
                            $insertValues = [
                                "'{$row[0]}'",        // RPTransNo
                                $this->formatDate($row[1]),    // RPTransDate
                                "'{$row[2]}'",        // Distributor
                                "'{$row[3]}'",        // Principal
                                "'{$row[4]}'",        // POTransNo
                                "'{$row[5]}'",        // InvoiceNo
                                "'{$row[6]}'",        // DRNo
                                "'{$row[7]}'",        // Remarks
                                "'{$row[8]}'",        // TotalCost
                                "'{$row[9]}'",        // RecUser
                                $this->formatDate($row[10]),    // RecDate
                                "'{$row[11]}'",       // ModUser
                                $this->formatDate($row[12]),    // ModDate
                                "'{$row[13]}'",       // PostStatus
                                "'{$row[14]}'",       // PostUser
                                $this->formatDate($row[15]),    // PostDate
                                "'{$row[16]}'",       // Reason
                                "'{$row[17]}'",       // TransType
                                $this->formatDate($row[18]),    // ActualReceiptDate
                            ];
                
                            // Explicitly list column names to match the $insertValues array
                            $columns = "RPTransNo, RPTransDate, Distributor, Principal, POTransNo, InvoiceNo, DRNo, Remarks, TotalCost, RecUser, RecDate, ModUser, ModDate, PostStatus, PostUser, PostDate, Reason, TransType, ActualReceiptDate";
                            $insertScript[] = "INSERT INTO SPM.ReceiptsPrincipalHDR ($columns) VALUES (" . implode(',', $insertValues) . ");";
                        }
                        $rowCount++;
                    }
                
                    $insertScript[] = "ROLLBACK TRAN;"; // End the transaction in the script
                
                    // Write INSERT statements to file
                    $outputFilename = 'receiptsprincipalhdr_insert_script.sql';
                    $filePath = base_path('app/Console/Commands/' . $outputFilename);
                    file_put_contents($filePath, implode("\n", $insertScript));
                
                    $this->info("Insert script successfully generated at: $filePath");
                
                } catch (\Exception $e) {
                    $this->error("Error generating insert script: {$e->getMessage()}");
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
                    $rowCount = 0;
                    $insertScript = ["BEGIN TRAN;"];
                
                    foreach ($file as $row) {
                        if ($rowCount > 0 && $row && !empty($row[0])) {
                            $insertValues = [
                                "'{$row[0]}'",        // RPTransNo
                                "'{$row[1]}'",        // ProductCode
                                "'{$row[2]}'",        // Location
                                "'{$row[3]}'",        // SOH
                                "'{$row[4]}'",        // StockUOM
                                "'{$row[5]}'",        // Qty
                                $this->formatDate($row[6]),    // ExpiryDate
                                "'{$row[7]}'",        // ExpiryYear
                                "'{$row[8]}'",        // ExpiryMonth
                                "'{$row[9]}'",        // ExpiryDay
                                $this->formatDate($row[10]),    // MfgDate
                                "'{$row[11]}'",       // TransUOM
                                "'{$row[12]}'",       // UnitCost
                                "'{$row[13]}'",       // ShelfLife
                                "'{$row[14]}'",       // MorD
                                "'{$row[15]}'",       // TotalCost
                                "'{$row[16]}'",       // RowNo
                                "'{$row[17]}'",       // Distributor
                                "'{$row[18]}'",       // BatchNo
                            ];
                
                            // Explicitly list column names to match the $insertValues array
                            $columns = "RPTransNo, ProductCode, Location, SOH, StockUOM, Qty, ExpiryDate, ExpiryYear, ExpiryMonth, ExpiryDay, MfgDate, TransUOM, UnitCost, ShelfLife, MorD, TotalCost, RowNo, Distributor, BatchNo";
                            $insertScript[] = "INSERT INTO SPM.ReceiptsPrincipalLIN ($columns) VALUES (" . implode(',', $insertValues) . ");";
                        }
                        $rowCount++;
                    }
                
                    $insertScript[] = "ROLLBACK TRAN;"; // End the transaction in the script
                
                    // Write INSERT statements to file
                    $outputFilename = 'receiptsprincipallin_insert_script.sql';
                    $filePath = base_path('app/Console/Commands/' . $outputFilename);
                    file_put_contents($filePath, implode("\n", $insertScript));
                
                    $this->info("Insert script successfully generated at: $filePath");
                
                } catch (\Exception $e) {
                    $this->error("Error generating insert script: {$e->getMessage()}");
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
