Script initialization:


<script type="text/javascript" src="https://widget.bliskapaczka.pl/v7/main.js"></script>



Stylesheet initialization:


<link rel="stylesheet" href="https://widget.bliskapaczka.pl/v7/main.css" />



Widget will be available through object assigned to global variable BPWidget.
To initialize widget use method:


BPWidget::init(element: HTMLElement, options: BPWidgetOptionsObject): void



Widget will be displayed in given html element. Element has to have width and height defined and should be displayed on page when map is initialized.
Example of usage:


BPWidget.init(
document.getElementById('bpWidget'),
{
callback: (point) => console.log(point),
posType: 'DELIVERY',
codOnly: false,
showCod: true,
language: 'ro',
operatorMarkers: true,
initialAddress: this.$ui.map.locality,
codeSearch: true,
countryCodes: 'RO',
operators: [
{
operator: 'DPD'
},
{
operator: 'SAMEDAY'
},
{
operator: 'FAN_COURIER'
},
{
operator: 'CARGUS'
},
],
alias: 'ecolet-192872'
}
);



This should give you a response with map_point_id which you will use while using endpoint /send-order 