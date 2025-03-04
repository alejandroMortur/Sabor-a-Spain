import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { CanvasJSAngularChartsModule } from '@canvasjs/angular-charts';
import { GrafService } from '../../../services/protected/graf.service';

@Component({
  selector: 'app-graf',
  imports: [CommonModule, CanvasJSAngularChartsModule],
  templateUrl: './graf.component.html',
  styleUrl: './graf.component.css'
})
export class GrafComponent {
  chartOptions: any;
  dataPoints: any[] = [];

  constructor(private grafService: GrafService) { }

  ngOnInit(): void {
    this.loadGraficoData();
  }

  // Método para cargar los datos y configurar el gráfico
  loadGraficoData(): void {
    this.grafService.getGraficoStock().subscribe(
      (data) => {
        // Transformamos los datos recibidos para adaptarlos al formato del gráfico
        this.dataPoints = data.map(item => ({
          name: item.name,
          y: item.y
        }));

        // Configuramos las opciones del gráfico con los datos obtenidos
        this.chartOptions = {
          animationEnabled: true,
          theme: "dark2",
          exportEnabled: true,
          title: {
            text: "Grafica total stock"
          },
          subtitles: [{
            text: "Grafico existencias stock de productos"
          }],
          data: [{
            type: "pie", // Puedes cambiar el tipo de gráfico si lo deseas
            indexLabel: "{name}: {y}",
            dataPoints: this.dataPoints
          }]
        };
      },
      (error) => {
        console.error('Error al cargar los datos del gráfico:', error);
      }
    );
  }
}
