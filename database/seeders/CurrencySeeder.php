<?php

use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->line("Insert currencies");
        $this->command->line("Reference: ISO - ISO 4217 Currency codes https://www.iso.org/iso-4217-currency-codes.html");
        $this->command->line("Use https://www.currency-iso.org/dam/downloads/lists/list_one.xml");

        $xml = file_get_contents('https://www.currency-iso.org/dam/downloads/lists/list_one.xml');
        $xmlObject = simplexml_load_string($xml);
        $xmlArray = json_decode( json_encode( $xmlObject ), TRUE );

        /*
        <ISO_4217 Pblshd="2018-08-29">
            <CcyTbl>
                <CcyNtry>
                    <CtryNm>AFGHANISTAN</CtryNm>
                    <CcyNm>Afghani</CcyNm>
                    <Ccy>AFN</Ccy>
                    <CcyNbr>971</CcyNbr>
                    <CcyMnrUnts>2</CcyMnrUnts>
                </CcyNtry>
            </CcyTbl>
        <ISO_4217>
        */

        $xmlCurrencies = $xmlArray['CcyTbl']['CcyNtry'];
        $currencies = [];
        foreach ($xmlCurrencies as $xmlCurrency) {
            if (!empty($xmlCurrency['Ccy'])) {
                $sort = 999;
                switch ($xmlCurrency['Ccy']) {
                    case 'MWK': $sort = 1; break;
                    case 'USD': $sort = 2; break;
                    case 'ZAR': $sort = 3; break;
                }
                $currencies[] = ['currency' => $xmlCurrency['CcyNm'], 'code' => $xmlCurrency['Ccy'], 'sort' => $sort];
            }
        }

        //Make unique
        $collection = new \Illuminate\Support\Collection($currencies);
        $unique = $collection->unique('code')->values()->all();

        DB::table('currencies')->delete();
        DB::table('currencies')->insert($unique);
    }
}
