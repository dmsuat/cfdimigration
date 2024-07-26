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
            return null; 
        }

        $timestamp = strtotime($dateString);
        return ($timestamp !== false) ? date('Y-m-d H:i:s', $timestamp) : null;
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
                    DB::beginTransaction();
                    foreach($file as $row) {
                        if ($rowCount > 0) {
                            if ($row && !empty($row[0])) {
                                // Create and save using the Eloquent model
                                LoadedSO::create([
                                    'Distributor'       => $row[0] ?? null,
                                    'Salesman'          => $row[1] ?? null,
                                    'Docno'             => $row[2] ?? null,
                                    'Location'          => $row[3] ?? null,
                                    'OrderDate'         => $row[4] ?? null,
                                    'ReqDelDate'        => $row[5] ?? null,
                                    'PaymentTerm'       => $row[6] ?? null,
                                    'AccountCode'       => $row[7] ?? null,
                                    'ProductCode'       => $row[8] ?? null,
                                    'NumberofOrders'    => $row[9] ?? null,
                                    'OrderUOM'          => $row[10] ?? null,
                                    'SystemDate'        => $row[11] ?? null,
                                    'SystemUser'        => $row[12] ?? null,
                                    'Rownum'            => $row[13] ?? null,
                                    'LoadedBy'          => $row[14] ?? null,
                                    'DateLoaded'        => $row[15] ?? null,
                                    'Status'            => $row[16] ?? null,
                                    'SONo'              => $row[17] ?? null,
                                    'SendStatus'        => $row[18] ?? null,
                                    'Remarks'           => $row[19] ?? null
                                ]);
                            }
                        }
                        $rowCount++;
                    }
                    // Commit the transaction
                    DB::commit();
                    $this->info("Rows successfully inserted into SPM.LoadedSO: $rowCount");
                    // dd($counter);
                } catch (\Exception $e) {
                    DB::rollBack();
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
                    
                    // Wrap the operation in a database transaction
                    $rowCount = 0;
                    DB::beginTransaction();
                    foreach($file as $row) {
                        if ($rowCount > 0) {
                            if ($row && !empty($row[0])) {
                                // Create and save using the Eloquent model
                                ARTRAN::create([
                                    'Docno'        => $row[0] ?? null,
                                    'Applyto'      => $row[1] ?? null,
                                    'Doctype'      => $row[2] ?? null,
                                    'Customercode' => $row[3] ?? null,
                                    'Paymentcode'  => $row[4] ?? null,
                                    'Docdate'      => $row[5] ?? null,
                                    'Duedate'      => $row[6] ?? null,
                                    'Docamt'       => $row[7] ?? null,
                                    'Recuser'      => $row[8] ?? null,
                                    'Recdate'      => $row[9] ?? null,
                                    'Dochome'      => $row[10] ?? null,
                                    'Distributor'  => $row[11] ?? null
                                ]);
                            }
                        }
                        $rowCount++;
                    }
                    // Commit the transaction
                    DB::commit();
                    $this->info("Rows successfully inserted into SPM.ARTRAN: $rowCount");
                    // dd($counter);
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
                        
                        // Wrap the operation in a database transaction
                        $rowCount = 0;
                        DB::beginTransaction();
                        foreach($file as $row) {
                            if ($rowCount > 0) {
                                if ($row && !empty($row[0])) {
                                    // Create and save using the Eloquent model
                                    SOHDR::create([
                                        'Docno'             => $row[0] ?? null
                                        ,'Docdate'          => isset($row[1]) ? date('Y-m-d H:i:s', strtotime($row[1])) : null
                                        ,'Customercode'     => $row[2] ?? null
                                        ,'Discountcode'     => $row[3] ?? null
                                        ,'Shipto'           => $row[4] ?? null
                                        ,'Poref'            => $row[5] ?? null
                                        ,'Paymentcode'      => $row[6] ?? null
                                        ,'Dropship'         => $row[7] ?? null
                                        ,'Procstat'         => $row[8] ?? null
                                        ,'Reqdate'          => isset($row[9]) ? date('Y-m-d H:i:s', strtotime($row[9])) : null
                                        ,'Totalsales'       => $row[10] ?? null
                                        ,'Lessdiscount'     => $row[11] ?? null
                                        ,'Salesdiscount'    => $row[12] ?? null
                                        ,'Addvat'           => $row[13] ?? null
                                        ,'Grandtotal'       => $row[14] ?? null
                                        ,'Spare1'           => $row[15] ?? null
                                        ,'Spare2'           => $row[16] ?? null
                                        ,'Spare3'           => $row[17] ?? null
                                        ,'Recuser'          => $row[18] ?? null
                                        ,'Recdate'          => isset($row[19]) ? date('Y-m-d H:i:s', strtotime($row[19])) : null
                                        ,'Moduser'          => $row[20] ?? null
                                        ,'Moddate'          => isset($row[21]) ? date('Y-m-d H:i:s', strtotime($row[21])) : null
                                        ,'Postuser'         => $row[22] ?? null
                                        ,'Postdate'         => isset($row[23]) ? date('Y-m-d H:i:s', strtotime($row[23])) : null
                                        ,'Printuser'        => $row[24] ?? null
                                        ,'Printdate'        => isset($row[25]) ? date('Y-m-d H:i:s', strtotime($row[25])) : null
                                        ,'PriceMatrixcode'  => $row[26] ?? null
                                        ,'Remarks'          => $row[27] ?? null
                                        ,'Distributor'      => $row[28] ?? null
                                        ,'Salesman'         => $row[29] ?? null
                                        ,'isPassed'         => $row[30] ?? null
                                        ,'PODate'           => isset($row[31]) ? date('Y-m-d H:i:s', strtotime($row[31])) : null
                                        ,'ForbidPartial'    => $row[32] ?? null
                                    ]);
                                }
                            }
                            $rowCount++;
                        }
                        // Commit the transaction
                        DB::commit();
                        $this->info("Rows successfully inserted into SPM.SOHDR: $rowCount");
                        // dd($counter);
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
                        
                        // Wrap the operation in a database transaction
                        $rowCount = 0;
                        DB::beginTransaction();
                        foreach($file as $row) {
                            if ($rowCount > 0) {
                                if ($row && !empty($row[0])) {
                                    // Create and save using the Eloquent model
                                    IVTRAN::create([
                                        'Distributor'      => $row[0] ?? null,
                                        'DocType'          => $row[1] ?? null,
                                        'Docdate'          => isset($row[2]) ? date('Y-m-d H:i:s', strtotime($row[2])) : null,
                                        'Docno'            => $row[3] ?? null,
                                        'ApplyTo'          => $row[4] ?? null,
                                        'Location'         => $row[5] ?? null,
                                        'Productcode'      => $row[6] ?? null,
                                        'ExpiryDate'       => isset($row[7]) ? date('Y-m-d H:i:s', strtotime($row[7])) : null,
                                        'Inventoriable'    => $row[8] ?? null,
                                        'Qty'              => $row[9] ?? null,
                                        'UnitCost'         => $row[10] ?? null,
                                        'StockUom'         => $row[11] ?? null,
                                        'TranUom'          => $row[12] ?? null,
                                        'Rownum'           => $row[13] ?? null,
                                        'Remarks'          => $row[14] ?? null,
                                        'Procstat'         => $row[15] ?? null,
                                        'PostUser'         => $row[16] ?? null,
                                        'PostDate'         => isset($row[17]) ? date('Y-m-d H:i:s', strtotime($row[17])) : null,
                                        'BatchNo'          => $row[18] ?? null,
                                    ]);
                                }
                            }
                            $rowCount++;
                        }
                        // Commit the transaction
                        DB::commit();
                        $this->info("Rows successfully inserted into SPM.IVTRAN: $rowCount");
                        // dd($counter);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error("Error inserting rows: {$e->getMessage()}");
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
                        
                        // Wrap the operation in a database transaction
                        $rowCount = 0;
                        DB::beginTransaction();
                        foreach($file as $row) {
                            if ($rowCount > 0) {
                                if ($row && !empty($row[0])) {
                                    // Create and save using the Eloquent model

                                    // SOLIN:: where('Docno', $row[0])
                                    //     ->where('CustomerCode', $row[22])
                                    //     ->where('Distributor', $row[23])
                                    //     ->where('RowNum', $row[17])
                                    //     ->delete();

                                    SOLIN::create([
                                        'DocNo'          => $row[0] ?? null,
                                        'LineType'       => $row[1] ?? null,
                                        'ProductCode'    => $row[2] ?? null,
                                        'Description'    => preg_replace('/[^\x00-\x7F\xA0-\xFF]/u', '', $row[3]),
                                        'Location'       => $row[4] ?? null,
                                        'StockUOM'       => $row[5] ?? null,
                                        'OrderUOM'       => $row[6] ?? null,
                                        'Qty'            => $row[7] ?? null,
                                        'UnitCost'       => $row[8] ?? null,
                                        'Amount'         => $row[9] ?? null,
                                        'Disc1'          => $row[10] ?? null,
                                        'Disc2'          => $row[11] ?? null,
                                        'Disc3'          => $row[12] ?? null,
                                        'Disc4'          => $row[13] ?? null,
                                        'Disc5'          => $row[14] ?? null,
                                        'Discount'       => $row[15] ?? null,
                                        'NetAmount'      => $row[16] ?? null,
                                        'RowNum'         => $row[17] ?? null,
                                        'Ref1'           => $row[18] ?? null,
                                        'Ref2'           => $row[19] ?? null,
                                        'Ref3'           => $row[20] ?? null,
                                        'RemQty'         => $row[21] ?? null,
                                        'CustomerCode'   => $row[22] ?? null,
                                        'Distributor'    => $row[23] ?? null,
                                        'RATE1'          => $row[24] ?? null,
                                        'RATE2'          => $row[25] ?? null,
                                        'RATE3'          => $row[26] ?? null,
                                        'RATE1BASIS'     => $row[27] ?? null,
                                        'RATE2BASIS'     => $row[28] ?? null,
                                        'RATE3BASIS'     => $row[29] ?? null,
                                    ]);
                                }
                            }
                            $rowCount++;
                        }
                        // Commit the transaction
                        DB::COMMIT();
                        $this->info("Rows successfully inserted into SPM.SOLIN: $rowCount");
                        // dd($counter);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error("Error inserting rows: {$e->getMessage()}");
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
                        
                        // Wrap the operation in a database transaction
                        $rowCount = 0;
                        DB::beginTransaction();
                        foreach($file as $row) {
                            if ($rowCount > 0) {
                                if ($row && !empty($row[0])) {
                                    // Create and save using the Eloquent model
                                    SOHDRH::create([
                                        'DocNo'            => $row[0] ?? null,
                                        'DocDate'          => isset($row[1]) ? date('Y-m-d H:i:s', strtotime($row[1])) : null,
                                        'CustomerCode'     => $row[2] ?? null,
                                        'DiscountCode'     => $row[3] ?? null,
                                        'ShipTo'           => $row[4] ?? null,
                                        'PORef'            => $row[5] ?? null,
                                        'PaymentCode'      => $row[6] ?? null,
                                        'DropShip'         => $row[7] ?? null,
                                        'Procstat'         => $row[8] ?? null,
                                        'ReqDate'          => isset($row[1]) ? date('Y-m-d H:i:s', strtotime($row[1])) : null,
                                        'TotalSales'       => $row[10] ?? null,
                                        'LessDiscount'     => $row[11] ?? null,
                                        'SalesDiscount'    => $row[12] ?? null,
                                        'AddVAT'           => $row[13] ?? null,
                                        'GrandTotal'       => $row[14] ?? null,
                                        'Spare1'           => $row[15] ?? null,
                                        'Spare2'           => $row[16] ?? null,
                                        'Spare3'           => $row[17] ?? null,
                                        'RecUser'          => $row[18] ?? null,
                                        'RecDate'          => isset($row[1]) ? date('Y-m-d H:i:s', strtotime($row[1])) : null,
                                        'ModUser'          => $row[20] ?? null,
                                        'ModDate'          => isset($row[1]) ? date('Y-m-d H:i:s', strtotime($row[1])) : null,
                                        'PostUser'         => $row[22] ?? null,
                                        'PostDate'         => isset($row[1]) ? date('Y-m-d H:i:s', strtotime($row[1])) : null,
                                        'PrintUser'        => $row[24] ?? null,
                                        'PrintDate'        => isset($row[1]) ? date('Y-m-d H:i:s', strtotime($row[1])) : null,
                                        'PriceMatrixCode'  => $row[26] ?? null,
                                        'Remarks'          => $row[27] ?? null,
                                        'Distributor'      => $row[28] ?? null,
                                        'Salesman'         => $row[29] ?? null,
                                        'isPassed'         => $row[30] ?? null,
                                        'PODate'           => isset($row[1]) ? date('Y-m-d H:i:s', strtotime($row[1])) : null,
                                        'ForbidPartial'    => $row[32] ?? null,
                                    ]);
                                }
                            }
                            $rowCount++;
                        }
                        // Commit the transaction
                        DB::commit();
                        $this->info("Rows successfully inserted into SPM.SOHDRH: $rowCount");
                        // dd($counter);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error("Error inserting rows: {$e->getMessage()}");
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
                    
                    // Wrap the operation in a database transaction
                    $rowCount = 0;
                    DB::beginTransaction();
                    $processedRecords = []; // Array to track processed combinations
                    foreach($file as $row) {
                        if ($rowCount > 0 && $row && !empty($row[0])) {
                            $recordKey = $row[0] . '_' . $row[23] . '_' . $row[17];
                            if (!in_array($recordKey, $processedRecords)) {
                                $processedRecords[] = $recordKey;
                                // Create and save using the Eloquent model
                                
                                SOLINH::create([
                                    'DocNo'          => $row[0] ?? null,
                                    'LineType'       => $row[1] ?? null,
                                    'ProductCode'    => $row[2] ?? null,
                                    'Description'    => preg_replace('/[^\x00-\x7F\xA0-\xFF]/u', '', $row[3]),
                                    'Location'       => $row[4] ?? null,
                                    'StockUOM'       => $row[5] ?? null,
                                    'OrderUOM'       => $row[6] ?? null,
                                    'Qty'            => $row[7] ?? null,
                                    'UnitCost'       => $row[8] ?? null,
                                    'Amount'         => $row[9] ?? null,
                                    'Disc1'          => $row[10] ?? null,
                                    'Disc2'          => $row[11] ?? null,
                                    'Disc3'          => $row[12] ?? null,
                                    'Disc4'          => $row[13] ?? null,
                                    'Disc5'          => $row[14] ?? null,
                                    'Discount'       => $row[15] ?? null,
                                    'NetAmount'      => $row[16] ?? null,
                                    'RowNum'         => $row[17] ?? null,
                                    'Ref1'           => $row[18] ?? null,
                                    'Ref2'           => $row[19] ?? null,
                                    'Ref3'           => $row[20] ?? null,
                                    'RemQty'         => $row[21] ?? null,
                                    'CustomerCode'   => $row[22] ?? null,
                                    'Distributor'    => $row[23] ?? null,
                                    'RATE1'          => $row[24] ?? null,
                                    'RATE2'          => $row[25] ?? null,
                                    'RATE3'          => $row[26] ?? null,
                                    'RATE1BASIS'     => $row[27] ?? null,
                                    'RATE2BASIS'     => $row[28] ?? null,
                                    'RATE3BASIS'     => $row[29] ?? null,
                                ]);
                            }
                        }
                        $rowCount++;
                    }
                    // Commit the transaction
                    DB::commit();
                    $this->info("Rows successfully inserted into SPM.SOLINH: $rowCount");
                    // dd($counter);
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error inserting rows: {$e->getMessage()}");
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
                        
                        // Wrap the operation in a database transaction
                        $rowCount = 0;
                        DB::beginTransaction();
                        foreach($file as $row) {
                            if ($rowCount > 0) {
                                if ($row && !empty($row[0])) {
                                    // Create and save using the Eloquent model
                                    SODRHDR::create([
                                        'Docno'            => $row[0]  ?? null,
                                        'Sono'             => $row[1]  ?? null,
                                        'Docdate'          => isset($row[2]) ? date('Y-m-d H:i:s', strtotime($row[2])) : null,
                                        'Customercode'     => $row[3]  ?? null,
                                        'Discountcode'     => $row[4]  ?? null,
                                        'Shipto'           => $row[5]  ?? null,
                                        'Poref'            => $row[6]  ?? null,
                                        'Paymentcode'      => $row[7]  ?? null,
                                        'Dropship'         => $row[8]  ?? null,
                                        'Procstat'         => $row[9]  ?? null,
                                        'Reqdate'          => isset($row[10]) ? date('Y-m-d H:i:s', strtotime($row[10])) : null,
                                        'Totalsales'       => $row[11] ?? null,
                                        'Lessdiscount'     => $row[12] ?? null,
                                        'Salesdiscount'    => $row[13] ?? null,
                                        'Addvat'           => $row[14] ?? null,
                                        'Grandtotal'       => $row[15] ?? null,
                                        'Spare1'           => $row[16] ?? null,
                                        'Spare2'           => $row[17] ?? null,
                                        'Spare3'           => $row[18] ?? null,
                                        'Recuser'          => $row[19] ?? null,
                                        'Recdate'          => isset($row[20]) ? date('Y-m-d H:i:s', strtotime($row[20])) : null,
                                        'Moduser'          => $row[21] ?? null,
                                        'Moddate'          => isset($row[22]) ? date('Y-m-d H:i:s', strtotime($row[22])) : null,
                                        'Postuser'         => $row[23] ?? null,
                                        'Postdate'         => isset($row[24]) ? date('Y-m-d H:i:s', strtotime($row[24])) : null,
                                        'Printuser'        => $row[25] ?? null,
                                        'Printdate'        => isset($row[26]) ? date('Y-m-d H:i:s', strtotime($row[26])) : null,
                                        'Sino'             => $row[27] ?? null,
                                        'Sidate'           => isset($row[28]) ? date('Y-m-d H:i:s', strtotime($row[28])) : null,
                                        'Duedate'          => isset($row[29]) ? date('Y-m-d H:i:s', strtotime($row[29])) : null,
                                        'Printaxuser'      => $row[30] ?? null,
                                        'Printaxdate'      => isset($row[31]) ? date('Y-m-d H:i:s', strtotime($row[31])) : null,
                                        'Remarks'          => $row[32] ?? null,
                                        'Grandhome'        => $row[33] ?? null,
                                        'NoSoref'          => $row[34] ?? null,
                                        'SiRef'            => $row[35] ?? null,
                                        'DRRef'            => $row[36] ?? null,
                                        'TransactionRef'   => $row[37] ?? null,
                                        'Distributor'      => $row[38] ?? null,
                                        'Salesman'         => $row[39] ?? null,
                                        'AmountPaid'       => $row[40] ?? null,
                                        'EWT'              => $row[41] ?? null,
                                        'ORNo'             => $row[42] ?? null,
                                    ]);
                                }
                            }
                            $rowCount++;
                        }
                        // Commit the transaction
                        DB::commit();
                        $this->info("Rows successfully inserted into SPM.SODRHDR: $rowCount");
                        // dd($counter);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error("Error inserting rows: {$e->getMessage()}");
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
                        
                        // Wrap the operation in a database transaction
                        $rowCount = 0;
                        DB::beginTransaction();
                        foreach($file as $row) {
                            if ($rowCount > 0) {
                                if ($row && !empty($row[0])) {
                                    // Create and save using the Eloquent model
                                    SODRLIN::CREATE([
                                        'Docno'         => $row[0]  ?? null,
                                        'Linetype'       => $row[1]  ?? null,
                                        'Productcode'    => $row[2]  ?? null,
                                        'Description'    => $row[3]  ?? null,
                                        'Location'       => $row[4]  ?? null,
                                        'Stockuom'       => $row[5]  ?? null,
                                        'Orderuom'       => $row[6]  ?? null,
                                        'Qty'           => $row[7]  ?? null,
                                        'Delqty'         => $row[8]  ?? null,
                                        'Backqty'        => $row[9]  ?? null,
                                        'Unitcost'       => $row[10] ?? null,
                                        'Amount'         => $row[11] ?? null,
                                        'Disc1'          => $row[12] ?? null,
                                        'Disc2'          => $row[13] ?? null,
                                        'Disc3'          => $row[14] ?? null,
                                        'Disc4'          => $row[15] ?? null,
                                        'Disc5'          => $row[16] ?? null,
                                        'Discount'       => $row[17] ?? null,
                                        'NetAmount'      => $row[18] ?? null,
                                        'Rownum'         => $row[19] ?? null,
                                        'Ref1'          => $row[20] ?? null,
                                        'Ref2'          => $row[21] ?? null,
                                        'Ref3'          => $row[22] ?? null,
                                        'Acctcode'       => $row[23] ?? null,
                                        'Sorownum'       => $row[24] ?? null,
                                        'Delpc'          => $row[25] ?? null,
                                        'Rempc'          => $row[26] ?? null,
                                        'Avecost'        => $row[27] ?? null,
                                        'ExpiryDate'     => $this->formatDate($row[28]), 
                                        'ExpYear'        => $row[29] ?? null,
                                        'ExpMonth'       => $row[30] ?? null,
                                        'ExpDay'         => $row[31] ?? null,
                                        'MonthDay'       => $row[32] ?? null,
                                        'PromoQty'       => $row[33] ?? null,
                                        'Distributor'    => $row[34] ?? null,
                                        'AllocationNo'   => $row[35] ?? null,
                                        'RATE1'          => $row[36] ?? null,
                                        'RATE2'          => $row[37] ?? null,
                                        'RATE3'          => $row[38] ?? null,
                                        'RATE1BASIS'     => $row[39] ?? null,
                                        'RATE2BASIS'     => $row[40] ?? null,
                                        'RATE3BASIS'     => $row[41] ?? null,
                                        'BatchNo'        => $row[42] ?? null,
                                    ]);
                                }
                            }
                            $rowCount++;
                        }
                        // dd($processedRecords);
                        // Commit the transaction
                        DB::COMMIT();
                        $this->info("Rows successfully inserted into SPM.SODRLIN: $rowCount");

                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error("Error inserting rows: {$e->getMessage()}");
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
                    
                    // Wrap the operation in a database transaction
                    $rowCount = 0;
                    DB::beginTransaction();
                    foreach($file as $row) {
                        if ($rowCount > 0) {
                            if ($row && !empty($row[0])) {
                                SOCreditCheck::create([
                                    'SONo'                => $row[0]  ?? null,
                                    'CustomerCode'        => $row[1]  ?? null,
                                    'CreditLimit'         => $row[2]  ?? null,
                                    'AvailableForSO'      => $row[3]  ?? null,
                                    'OpenApprovedSO'      => $row[4]  ?? null,
                                    'PendingDR'           => $row[5]  ?? null,
                                    'ARAmount'            => $row[6]  ?? null,
                                    'ApprovedDR'          => $row[7]  ?? null,
                                    'ApprovedDM'          => $row[8]  ?? null,
                                    'ApprovedCM'          => $row[9]  ?? null,
                                    'ApprovedARBegBal'    => $row[10] ?? null,
                                    'ApprovedCol'         => $row[11] ?? null,
                                    'ApprovedColPDC'      => $row[12] ?? null,
                                    'ReturnofGoodStock'   => $row[13] ?? null,
                                    'ReturnofBadStock'    => $row[14] ?? null,
                                    'DMQAJ'               => $row[15] ?? null,
                                    'CMQAJ'               => $row[16] ?? null,
                                    'PrePayment'          => $row[17] ?? null,
                                    'BaseAging'           => $row[18] ?? null, 
                                    'ActualAging'         => $row[19] ?? null, 
                                    'CreditStatus'        => $row[20] ?? null,
                                    'AgingStatus'         => $row[21] ?? null,
                                    'Distributor'         => $row[22] ?? null,
                                ]);
                            }
                        }
                        $rowCount++;
                    }
                    DB::commit();
                    $this->info("Rows successfully inserted into SPM.SOCreditCheck: $rowCount");

                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error inserting rows: {$e->getMessage()}");
                }
                break;
            default :
                
        }
    }
}
