import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { CanvasJSAngularChartsModule } from '@canvasjs/angular-charts';

@Component({
  selector: 'app-bar',
  imports: [CommonModule, CanvasJSAngularChartsModule],
  templateUrl: './bar.component.html',
  styleUrl: './bar.component.css'
})
export class BarComponent {
  chart: any;

  chartOptions = {
    title: {
      text: "Total de Ventas por categorias"
    },
    animationEnabled: true,
    axisY: {
      includeZero: true,
      suffix: "€"
    },
    data: [{
      type: "bar",
      indexLabel: "{y}",
      yValueFormatString: "#,###€",
      dataPoints: [
        { label: "Cárnicos", y: 15 },
        { label: "Bebidas", y: 20 },
        { label: "Lácteos", y: 24 },
        { label: "Frutas", y: 29 },
        { label: "Verduras", y: 73 },
        { label: "Pescados", y: 80 }
      ]
    }]
  }
}
