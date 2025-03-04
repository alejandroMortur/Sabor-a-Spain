import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { CanvasJS, CanvasJSAngularChartsModule } from '@canvasjs/angular-charts';
import { SalesBarService } from '../../../services/protected/sales-bar.service';

@Component({
  selector: 'app-bar',
  imports: [CommonModule, CanvasJSAngularChartsModule],
  templateUrl: './bar.component.html',
  styleUrl: './bar.component.css'
})
export class BarComponent {
  chart: any;
  chartOptions: any = {
    title: {
      text: "Total de Ventas por Categorías"
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
      dataPoints: []  // Aquí guardaremos los puntos de datos
    }]
  };

  constructor(private salesBarService: SalesBarService) { }

  ngOnInit(): void {
    // Llamamos al servicio para obtener los datos de ventas
    this.salesBarService.getVentas().subscribe(data => {
      // Actualizamos los datos del gráfico directamente
      this.chartOptions.data[0].dataPoints = data;  // Usamos los datos tal cual, sin necesidad de mapear
    });
  }

  ngAfterViewInit(): void {
    // Esperamos que el DOM esté completamente cargado antes de crear el gráfico
    this.chart = new CanvasJS.Chart("chartContainer", this.chartOptions);
    this.chart.render();
  }
}